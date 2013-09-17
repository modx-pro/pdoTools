<?php
/* @var array $scriptProperties */
if (!empty($input)) {$id = $input;}

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

if (!empty($top) || !empty($topLevel)) {
	// Select needed context for parents functionality
	if (empty($context)) {
		$q = $modx->newQuery('modResource', $id);
		$q->select('context_key');
		if ($q->prepare() && $q->stmt->execute()) {
			$context = $q->stmt->fetch(PDO::FETCH_COLUMN);
		}
	}
	// This algorithm taken from snippet "UltimateParent"
	$top = isset($top) && intval($top) ? $top : 0;
	$topLevel= isset($topLevel) && intval($topLevel) ? intval($topLevel) : 0;
	$pid = $id;
	$pids = $modx->getParentIds($id, 10, array('context' => $context));
	if (!$topLevel || count($pids) >= $topLevel) {
		while ($parentIds= $modx->getParentIds($id, 1, array('context' => $context))) {
			$pid = array_pop($parentIds);
			if ($pid == $top) {
				break;
			}
			$id = $pid;
			$parentIds = $modx->getParentIds($id, 10, array('context' => $context));
			if ($topLevel && count($parentIds) < $topLevel) {
				break;
			}
		}
	}
}

/* @var pdoFetch $pdoFetch */
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

$class = 'modResource';
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
if (in_array($field, $resourceColumns)) {
	$select = array($class => $field);
	$includeTVs = '';
}
else {
	$select = array($class => 'id');
	$includeTVs = $field;
}

// Default parameters
$default = array(
	'class' => $class,
	'where' => $modx->toJSON($where),
	'select' => $modx->toJSON($select),
	'includeTVs' => $includeTVs,
	'sortby' => $class.'.id',
	'sortdir' => 'asc',
	'return' => 'data',
	'totalVar' => 'pdofield.total',
	'limit' => 1,
);

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$row = $pdoFetch->run();

if (is_array($row)) {
	if (!empty($row[0]) && (empty($includeTVs) || count($row[0]) == 2)) {
		return array_pop($row[0]);
	}
}
else {
	return $row;
}