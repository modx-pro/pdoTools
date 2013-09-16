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
if (empty($id)) {return '';}
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
	// Select id of parent of specified id on level = &top
	if (!empty($top)) {
		$parents = $modx->getParentIds($id, $top, array('context' => $context));
		if (empty($parents)) {return '';}
		$id = array_pop($parents);
	}
	// Select id of parent of specified id from root on level = &toplevel
	elseif (!empty($topLevel)) {
		$childs = array_flip($modx->getChildIds(0, $topLevel, array('context' => $context)));
		if (empty($childs)) {return '';}
		while ($parents = $modx->getParentIds($id, 1, array('context' => $context))) {
			$id = array_pop($parents);
			if (!isset($childs[$id])) {
				$parents = $modx->getParentIds($id, 1, array('context' => $context));
			}
			else {
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