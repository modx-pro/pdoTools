<?php
/** @var array $scriptProperties */
if (empty($outputSeparator)) {$outputSeparator = ' / ';}
if (empty($titleField)) {$titleField = 'longtitle';}
if (!empty($limit)) {$limit = (int) $limit;}
if (empty($limit)) {$limit = 3;}
if (!isset($pageVarKey)) {$pageVarKey = 'page';}
if (!isset($queryVarKey)) {$queryVarKey = 'query';}
if (empty($tplPages)) {$tplPages = '@INLINE [[%pdopage_page]] [[+page]] [[%pdopage_from]] [[+pageCount]]';}
if (empty($tplSearch)) {$tplSearch = '@INLINE «[[+mse2_query]]»';}
if (empty($minQuery)) {$minQuery = 3;}
if (empty($id)) {$id = $modx->resource->id;}
if (empty($cacheKey)) {$cacheKey = 'title_crumbs';}
if (!isset($cacheTime)) {$cacheTime = 0;}
$fqn = $modx->getOption('pdoTools.class', null, 'pdotools.pdotools', true);
if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
	$pdoTools = new $pdoClass($modx, $scriptProperties);
}
elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
	$pdoTools = new $pdoClass($modx, $scriptProperties);
}
else {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoTools from "MODX_CORE_PATH/components/pdotools/model/".');
	return false;
}
$modx->lexicon->load('pdotools:pdopage');

/** @var modResource $resource */
$resource = ($id == $modx->resource->id)
	? $modx->resource
	: $modx->getObject('modResource', $id);
if (!$resource) {return '';}

$title = array();
$pagetitle = trim($resource->get($titleField));
if (empty($pagetitle)) {
	$pagetitle = $resource->get('pagetitle');
}

// Add search request if exists
if (!empty($_GET[$queryVarKey]) && strlen($_GET[$queryVarKey]) >= $minQuery && !empty($tplSearch)) {
	$pagetitle .= ' ' . $pdoTools->getChunk($tplSearch, array(
		$queryVarKey => $modx->stripTags($_GET[$queryVarKey])
	));
}
$title[] = $pagetitle;

// Add pagination if exists
if (!empty($_GET[$pageVarKey]) && !empty($tplPages)) {
	$title[] = $pdoTools->getChunk($tplPages, array(
		'page' => intval($_GET[$pageVarKey])
	));
}

// Add parents
$cacheKey = $resource->getCacheKey() . '/' . $cacheKey;
$cacheOptions = array('cache_key' => $modx->getOption('cache_resource_key', null, 'resource'));
$crumbs = '';
if (empty($cache) || !$crumbs = $modx->cacheManager->get($cacheKey, $cacheOptions)) {
	$crumbs = $modx->runSnippet('pdoCrumbs', array(
		'to' => $resource->id,
		'limit' => $limit,
		'outputSeparator' => $outputSeparator,
		'showHome' => 0,
		'showAtHome' => 0,
		'showCurrent' => 0,
		'direction' => 'rtl',
		'tpl' => '@INLINE [[+menutitle]]',
		'tplCurrent' => '@INLINE [[+menutitle]]',
		'tplMax' => '',
		'tplHome' => '',
		'tplWrapper' => '@INLINE [[+output]]',
	));
}
if (!empty($crumbs)) {
	if (!empty($cache)) {
		$modx->cacheManager->set($cacheKey, $crumbs, $cacheTime, $cacheOptions);
	}
	$title[] = $crumbs;
}

if (!empty($registerJs)) {
	$config = array(
		'separator' => $outputSeparator,
		'tpl' => str_replace(array('[[+', ']]'), array('{', '}'), $pdoTools->getChunk($tplPages))
	);
	$modx->regClientStartupScript('<script type="text/javascript">pdoTitle = ' . $modx->toJSON($config) . ';</script>', true);
}

return implode($outputSeparator, $title);