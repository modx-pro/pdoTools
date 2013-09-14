<?php
/* @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

$class = 'modResource';
// Start building "Where" expression
$where = array();
if (empty($showUnpublished)) {$where['published'] = 1;}
if (empty($showHidden)) {$where['hidemenu'] = 0;}
if (empty($showDeleted)) {$where['deleted'] = 0;}
if (!empty($hideContainers)) {$where['isfolder'] = 0;}
$context = !empty($context) ? array_map('trim', explode(',',$context)) : array($modx->context->key);

// Filter by ids
if (!empty($resources)) {
	$resources = array_map('trim', explode(',', $resources));
	$resources_in = $resources_out = array();
	foreach ($resources as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$resources_out[] = abs($v);}
		else {$resources_in[] = $v;}
	}
	if (!empty($resources_in)) {$where[$class.'.id:IN'] = $resources_in;}
	if (!empty($resources_out)) {$where[$class.'.id:NOT IN'] = $resources_out;}
}
// Filter by parents
if (!isset($parents) || $parents == '') {$parents = $modx->resource->id;}
if (!empty($parents)) {
	$pids = array();
	$parents = array_map('trim', explode(',', $parents));
	$parents_in = $parents_out = array();
	foreach ($parents as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$parents_out[] = abs($v);}
		else {$parents_in[] = $v;}
	}
	if (!empty($depth) && $depth > 0) {
		$q = $modx->newQuery($class, array('id:IN' => array_merge($parents_in, $parents_out)));
		$q->select('id,context_key');
		if ($q->prepare() && $q->stmt->execute()) {
			while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
				$pids[$row['id']] = $row['context_key'];
			}
		}
		foreach ($pids as $k => $v) {
			if (!is_numeric($k)) {continue;}
			elseif (in_array($k, $parents_in)) {
				$parents_in = array_merge($parents_in, $modx->getChildIds($k, $depth, array('context' => $v)));
			}
			else {
				$parents_out = array_merge($parents_out, $modx->getChildIds($k, $depth, array('context' => $v)));
			}
		}
	}
	if (!empty($parents_in)) {$where[$class.'.parent:IN'] = $parents_in;}
	if (!empty($parents_out)) {$where[$class.'.parent:NOT IN'] = $parents_out;}
}
// Limit query by context, if no resources or parents set
if (empty($resources) && empty($parents)) {
	$where['context_key:IN'] = $context;
}

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
if (empty($includeContent)) {
	$key = array_search('content', $resourceColumns);
	unset($resourceColumns[$key]);
}
$select = array($class => implode(',',$resourceColumns));

// Add custom parameters
foreach (array('where','select') as $v) {
	if (!empty($scriptProperties[$v])) {
		$tmp = $modx->fromJSON($scriptProperties[$v]);
		if (is_array($tmp)) {
			$$v = array_merge($$v, $tmp);
		}
	}
	unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

// Default parameters
$default = array(
	'class' => $class,
	'where' => $modx->toJSON($where),
	'select' => $modx->toJSON($select),
	'groupby' => $class.'.id',
	'sortby' => $class.'.id',
	'sortdir' => 'DESC',
	'return' => !empty($returnIds) ? 'ids' : 'chunks',
);

if (!empty($resources_in) && (empty($scriptProperties['sortby']) || $scriptProperties['sortby'] == 'id')) {
	$scriptProperties['sortby'] = "find_in_set(`$class`.`id`,'".implode(',', $resources_in)."')";
	$scriptProperties['sortdir'] = '';
}

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$output = $pdoFetch->run();

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$log .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($returnIds)) {
	$modx->setPlaceholder('pdoResources.log', $log);
	return $output;
}
elseif (!empty($toSeparatePlaceholders)) {
	$output['log'] = $log;
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else {
	$output .= $log;

	if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
		$output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
	}

	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}