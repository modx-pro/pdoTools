<?php
/* @var array $scriptProperties */
if (!empty($input)) {$id = $input;}
if (!isset($default)) {$default = '';}
if (!isset($output)) {$output = '';}
$class = $modx->getOption('class', $scriptProperties, 'modResource', true);
$isResource = in_array($class, $modx->getDescendants('modResource'));

if (empty($field)) {$field = 'pagetitle';}
if (!empty($options)) {
	$options = trim($options);
	if ($options[0] == '{') {
		$tmp = $modx->fromJSON($options);
		if (is_array($tmp)) {
			extract($tmp);
			$scriptProperties = array_merge($scriptProperties, $tmp);
		}
	}
	else {
		$field = $options;
	}
}
if (empty($id)) {$id = $modx->resource->id;}
if (!isset($context)) {$context = '';}

if ((!empty($top) || !empty($topLevel)) && $isResource) {
	// Select needed context for parents functionality
	if (empty($context)) {
		$q = $modx->newQuery($class, $id);
		$q->select('context_key');
		$tstart = microtime(true);
		if ($q->prepare() && $q->stmt->execute()) {
			$modx->queryTime += microtime(true) - $tstart;
			$modx->executedQueries++;
			$context = $q->stmt->fetch(PDO::FETCH_COLUMN);
		}
	}
	// Gets the parent from root of context, if specified
	if (!empty($topLevel)) {
		$pids = $modx->getChildIds(0, $topLevel, array('context' => $context));
		$pid = $id;
		while (true) {
			$tmp = $modx->getParentIds($pid, 1, array('context' => $context));
			if (!$pid = current($tmp)) {
				break;
			}
			elseif (in_array($pid, $pids)) {
				$id = $pid;
				break;
			}
		}
	}
	elseif (!empty($top)) {
		$pid = $id;
		for ($i = 1; $i <= $top; $i++) {
			$tmp = $modx->getParentIds($pid, 1, array('context' => $context)); 
			if (!$pid = current($tmp)) {
				break;
			}
			$id = $pid;
		}
	}
}

/* @var pdoFetch $pdoFetch */
if (!$modx->getService('pdoFetch')) {return false;}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

$where = array($class.'.id' => $id);
// Add custom parameters
foreach (array('where') as $v) {
	if (!empty($scriptProperties[$v])) {
		$tmp = $modx->fromJSON($scriptProperties[$v]);
		if (is_array($tmp)) {
			$$v = array_merge($$v, $tmp);
		}
	}
	unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
$field = strtolower($field);
if (in_array($field, $resourceColumns)) {
	$scriptProperties['select'] = array($class => $field);
	$scriptProperties['includeTVs'] = '';
}
elseif ($isResource) {
	$scriptProperties['select'] = array($class => 'id');
	$scriptProperties['includeTVs'] = $field;
}
// Additional default field
if (!empty($default)) {
	$default = strtolower($default);
	if (in_array($default, $resourceColumns)) {
		$scriptProperties['select'][$class] .= ','.$default;
	}
	elseif ($isResource) {
		$scriptProperties['includeTVs'] = empty($scriptProperties['includeTVs'])
			? $default
			: $scriptProperties['includeTVs'] . ',' . $default;
	}
}

$scriptProperties['disableConditions'] = true;
if ($row = $pdoFetch->getObject($class, $where, $scriptProperties)) {
	foreach ($row as $k => $v) {
		$k = strtolower($k);
		if ($k == $field && $v != '') {
			$output = $v;
			break;
		}
		elseif ($k == $default && $v != '') {
			$output = $v;
			break;
		}
	}
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}