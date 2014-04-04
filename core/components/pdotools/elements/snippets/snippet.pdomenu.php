<?php
/** @var array $scriptProperties */

// Convert parameters from Wayfinder if exists
if (isset($startId)) {
	$scriptProperties['parents'] = $startId;
}
if (!empty($includeDocs)) {
	$tmp = array_map('trim', explode(',', $includeDocs));
	foreach ($tmp as $v) {
		if (!empty($scriptProperties['resources'])) {
			$scriptProperties['resources'] .= ','.$v;
		}
		else {
			$scriptProperties['resources'] = $v;
		}
	}
}
if (!empty($excludeDocs)) {
	$tmp = array_map('trim', explode(',', $excludeDocs));
	foreach ($tmp as $v) {
		if (!empty($scriptProperties['resources'])) {
			$scriptProperties['resources'] .= ',-'.$v;
		}
		else {
			$scriptProperties['resources'] = '-'.$v;
		}
	}
}

if (!empty($previewUnpublished) && $modx->hasPermission('view_unpublished')) {
	$scriptProperties['showUnpublished'] = 1;
}

$scriptProperties['depth'] = empty($level) ? 100 : abs($level) - 1;
if (!empty($contexts)) {$scriptProperties['context'] = $contexts;}
if (empty($scriptProperties['context'])) {$scriptProperties['context'] = $modx->resource->context_key;}

// Save original parents specified by user
$specified_parents = array_map('trim', explode(',', $scriptProperties['parents']));

if ($scriptProperties['parents'] === '') {
	$scriptProperties['parents'] = $modx->resource->id;
}
elseif ($scriptProperties['parents'] === 0 || $scriptProperties['parents'] === '0') {
	if ($scriptProperties['depth'] !== '' && $scriptProperties['depth'] !== 100) {
		$contexts = array_map('trim', explode(',', $scriptProperties['context']));
		$parents = array();
		foreach ($contexts as $ctx) {
			$parents = array_merge($parents, $modx->getChildIds(0, $scriptProperties['depth'], array('context' => $ctx)));
		}
		$scriptProperties['parents'] = !empty($parents) ? implode(',', $parents) : '+0';
		$scriptProperties['depth'] = 0;
	}
	$scriptProperties['includeParents'] = 1;
	$scriptProperties['displayStart'] = 0;
}
else {
	$parents = array_map('trim', explode(',', $scriptProperties['parents']));
	$parents_in = $parents_out = array();
	foreach ($parents as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v[0] == '-') {$parents_out[] = abs($v);}
		else {$parents_in[] = abs($v);}
	}

	if (empty($parents_in)) {
		$scriptProperties['includeParents'] = 1;
		$scriptProperties['displayStart'] = 0;
	}
}

if (!empty($displayStart)) {$scriptProperties['includeParents'] = 1;}
if (!empty($ph)) {$toPlaceholder = $ph;}
if (!empty($sortOrder)) {$scriptProperties['sortdir'] = $sortOrder;}
if (!empty($sortBy)) {$scriptProperties['sortby'] = $sortBy;}
if (!empty($permissions)) {$scriptProperties['checkPermissions'] = $permissions;}
if (!empty($cacheResults)) {$scriptProperties['cache'] = $cacheResults;}
if (!empty($ignoreHidden)) {$scriptProperties['showHidden'] = $ignoreHidden;}

$wfTemplates = array(
	'outerTpl' => 'tplOuter',
	'rowTpl' => 'tpl',
	'parentRowTpl' => 'tplParentRow',
	'parentRowHereTpl' => 'tplParentRowHere',
	'hereTpl' => 'tplHere',
	'innerTpl' => 'tplInner',
	'innerRowTpl' => 'tplInnerRow',
	'innerHereTpl' => 'tplInnerHere',
	'activeParentRowTpl' => 'tplParentRowActive',
	'categoryFoldersTpl' => 'tplCategoryFolder',
	'startItemTpl' => 'tplStart'
);
foreach ($wfTemplates as $k => $v) {
	if (isset(${$k})) {
		$scriptProperties[$v] = ${$k};
	}
}

//---

/** @var pdoMenu $pdoMenu */
if (!$modx->loadClass('pdotools.pdoMenu', MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoMenu from "MODX_CORE_PATH/components/pdotools/model/".');
	return false;
}
$pdoMenu = new pdoMenu($modx, $scriptProperties);
$pdoMenu->pdoTools->addTime('pdoTools loaded');

$output = !empty($cache) || !$modx->user->id && !empty($cacheAnonymous)
	? $output = $pdoMenu->pdoTools->getCache($scriptProperties)
	: '';

if (empty($output)) {
	$rows = $pdoMenu->pdoTools->run();
	$tmp = $pdoMenu->pdoTools->buildTree($rows);
	$tree = array();
	foreach ($tmp as $k => $v) {
		if (empty($v['id'])) {
			if (!in_array($k, $specified_parents) && !$pdoMenu->checkResource($k)) {
				continue;
			}
			else {
				$tree = array_merge($tree, $v['children']);
			}
		}
		else {
			$tree[$k] = $v;
		}
	}

	$output = $pdoMenu->templateTree($tree);
	if (!empty($cache) || !$modx->user->id && !empty($cacheAnonymous)) {
		$pdoMenu->pdoTools->setCache($output, $scriptProperties);
	}
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoMenuLog">' . print_r($pdoMenu->pdoTools->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}