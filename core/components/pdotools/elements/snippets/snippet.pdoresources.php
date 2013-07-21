<?php
/* @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdofetch','pdoFetch', MODX_CORE_PATH.'components/pdotools/model/pdotools/',$scriptProperties);
$pdoFetch->addTime('pdoTools loaded.');

$class = 'modResource';
// Start building "Where" expression
$where = array();
if (empty($showUnpublished)) {$where['published'] = 1;}
if (empty($showHidden)) {$where['hidemenu'] = 0;}
if (empty($showDeleted)) {$where['deleted'] = 0;}
if (!empty($hideContainers)) {$where['isfolder'] = 0;}
$context = !empty($context) ? array_map('trim', explode(',',$context)) : array($modx->context->key);

// Filter by ids
if (!empty($resources)){
	$resources = array_map('trim', explode(',', $resources));
	$in = $out = array();
	foreach ($resources as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$out[] = abs($v);}
		else {$in[] = $v;}
	}
	if (!empty($in)) {$where['id:IN'] = $in;}
	if (!empty($out)) {$where['id:NOT IN'] = $out;}
}
// Filter by parents
if (!isset($parents) || $parents == '') {$parents = $modx->resource->id;}
if (!empty($parents)) {
	$pids = array();
	$parents = array_map('trim', explode(',', $parents));
	if (!empty($depth) && $depth > 0) {
		$q = $modx->newQuery('modResource', array('id:IN' => $parents));
		$q->select('id,context_key');
		if ($q->prepare() && $q->stmt->execute()) {
			while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
				$pids[$row['id']] = $row['context_key'];
			}
		}
		foreach ($pids as $k => $v) {
			if (!is_numeric($k)) {continue;}
			$parents = array_merge($parents, $modx->getChildIds($k, $depth, array('context' => $v)));
		}
	}
	$where['parent:IN'] = $parents;
}
// Limit query by context, if no resources or parents set
if (empty($resources) && empty($parents)) {
	$where['context_key:IN'] = $context;
}

// Adding custom where parameters
if (!empty($scriptProperties['where'])) {
	$tmp = $modx->fromJSON($scriptProperties['where']);
	if (is_array($tmp)) {
		$where = array_merge($where, $tmp);
	}
}
unset($scriptProperties['where']);
$pdoFetch->addTime('"Where" expression built.');
// End of building "Where" expression

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
if (empty($includeContent)) {
	$key = array_search('content', $resourceColumns);
	unset($resourceColumns[$key]);
}
$select = array($class => implode(',',$resourceColumns));
if (!empty($scriptProperties['select'])) {
	$tmp = $modx->fromJSON($scriptProperties['select']);
	if (is_array($tmp)) {
		$select = array_merge($select, $tmp);
	}
	else {$select = array($scriptProperties['select']);}
}
unset($scriptProperties['select']);

// Default parameters
$default = array(
	'class' => $class
	,'where' => $modx->toJSON($where)
	,'select' => $modx->toJSON($select)
	,'sortby' => $class.'.id'
	,'sortdir' => 'DESC'
	//,'groupby' => $class.'.id'
	,'return' => !empty($returnIds) ? 'ids' : 'chunks'
);

if (!empty($in) && (empty($scriptProperties['sortby']) || $scriptProperties['sortby'] == 'id')) {
	$scriptProperties['sortby'] = "find_in_set(`$class`.`id`,'".implode(',', $in)."')";
	$scriptProperties['sortdir'] = '';
}

// Merge all properties and run!
$pdoFetch->addTime('Query parameters are prepared.');
$pdoFetch->setConfig(array_merge($default, $scriptProperties));

$output = $pdoFetch->run();
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($toSeparatePlaceholders)) {
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
	$output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}