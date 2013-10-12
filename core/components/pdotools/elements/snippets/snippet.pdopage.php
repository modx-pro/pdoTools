<?php
/** @var array $scriptProperties */
/** @var pdoTools $pdoTools */
if (!$modx->getService('pdoTools')) {return false;}
$pdoTools = new pdoTools($modx, $scriptProperties);
$pdoTools->addTime('pdoTools loaded');

/** Default values */
if (empty($pageVarKey)) {$pageVarKey = 'page';}
if (empty($totalVar)) {$totalVar = 'total';}


/** Verify values */
// Check fot correct page
if (isset($_REQUEST[$pageVarKey]) && !is_numeric($_REQUEST[$pageVarKey])) {
	unset($_GET[$pageVarKey], $_GET[$modx->getOption('request_param_alias',null,'q')]);
	$modx->sendRedirect($modx->makeUrl($modx->resource->id, $modx->context->key, $_GET, 'full'));
}
elseif (!empty($_REQUEST[$pageVarKey])) {
	$page = (integer) $_REQUEST[$pageVarKey];
}

if (!empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit'])) {
	$scriptProperties['limit'] = $_REQUEST['limit'];
}
/*
if (!empty($pageOneLimit) && !empty($page) && $page == 1) {
	$scriptProperties['limit'] = $pageOneLimit;
}
*/
if (!empty($maxLimit) && !empty($scriptProperties['limit']) && $scriptProperties['limit'] > $maxLimit) {
	$scriptProperties['limit'] = $maxLimit;
}

// Offset does not not works without limit
if (!empty($offset) && empty($scriptProperties['limit'])) {
	$scriptProperties['limit'] = 10000000;
}


$output = '';
if ($object = $modx->getObject('modSnippet', array('name' => $scriptProperties['element']))) {
	$object->setCacheable(false);

	if (!empty($toPlaceholder)) {
		$object->process($scriptProperties);
		$output = $modx->getPlaceholder($toPlaceholder);
	} else {
		$output = $object->process($scriptProperties);
	}
}
else {
	$modx->log(modX::LOG_LEVEL_ERROR, '[pdoPage] Could not load element "'.$scriptProperties['element'].'"');
}


return $output;