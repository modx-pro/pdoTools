<?php
/* @var array $scriptProperties */
if (!empty($input)) {$id = $input;}
if (!isset($default)) {$default = '';}
if (!isset($output)) {$output = '';}
$class = $modx->getOption('class', $scriptProperties, 'modResource', true);
$isResource = $class == 'modResource' || in_array($class, $modx->getDescendants('modResource'));

if (empty($field)) {$field = 'pagetitle';}
if (!isset($topLevel)) {$topLevel = '';}
if (!isset($top)) {$top = '';}
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

if (($top !== '' || $topLevel !== '') && $isResource) {
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
	$top = intval($top) ? intval($top) : 0;
	$topLevel = intval($topLevel) ? intval($topLevel) : 0;
	if ($top && !$topLevel) {
		$pid = $id;
		for ($i = 1; $i <= $top; $i++) {
			$tmp = $modx->getParentIds($pid, 1, array('context' => $context));
			if (!$pid = current($tmp)) {
				break;
			}
			$id = $pid;
		}
	}
	// This logic taken from snippet UltimateParent
	// Thanks to its authors!
	elseif ($id != $top) {
		$pid = $id;
		$pids = $modx->getParentIds($id, 10, array('context' => $context));
		if (!$topLevel || count($pids) >= $topLevel) {
			while ($parentIds = $modx->getParentIds($id, 1, array('context' => $context))) {
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
	// --
}

/* @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
	$pdoFetch = new $pdoClass($modx, $scriptProperties);
}
elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
	$pdoFetch = new $pdoClass($modx, $scriptProperties);
}
else {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoFetch from "MODX_CORE_PATH/components/pdotools/model/".');
	return false;
}
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
		if (strtolower($k) == $field && $v != '') {
			$output = $v;
			break;
		}
	}

	if (empty($output) && !empty($default)) {
		foreach ($row as $k => $v) {
			if (strtolower($k) == $default && $v != '') {
				$output = $v;
				break;
			}
		}
	}
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}