<?php
/** @var array $scriptProperties */
/** @var pdoPage $pdoPage */
if (!$modx->loadClass('pdoPage', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoPage = new pdoPage($modx, $scriptProperties);
$pdoPage->addTime('pdoTools loaded');

/** Default values */
if (empty($pageVarKey)) {$pageVarKey = 'page';}
if (empty($pageNavVar)) {$pageNavVar = 'page.nav';}
if (empty($totalVar)) {$totalVar = 'total';}
if (empty($page)) {$page = 1;}
if (empty($scheme)) {$scheme = -1;}
elseif (is_numeric($scheme)) {$scheme = (integer) $scheme;}

/** Verify values */
// Page
if (isset($_REQUEST[$pageVarKey]) && (!is_numeric($_REQUEST[$pageVarKey]) || $_REQUEST[$pageVarKey] <= 1)) {
	return $pdoPage->redirectToFirst();
}
elseif (!empty($_REQUEST[$pageVarKey])) {
	$page = (integer) $_REQUEST[$pageVarKey];
}
unset($_REQUEST[$pageVarKey]);

// Limit
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

// Offset
if ($page > 1) {
	$scriptProperties['offset'] = empty($offset)
		? $scriptProperties['limit'] * $page - $scriptProperties['limit']
		: $scriptProperties['limit'] * $page - $scriptProperties['limit'] + $offset;
}
if (!empty($scriptProperties['offset']) && empty($scriptProperties['limit'])) {
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

/** Pagination */
$url = $pdoPage->getBaseUrl();

$total = $modx->getPlaceholder($totalVar);
$pages = ceil($total / $scriptProperties['limit']);

// Redirect to start if somebody specified incorrect page
if ($page > $pages) {
	return $pdoPage->redirectToFirst();
}

$pagination = array(
	'first' => $page > 2 && !empty($tplPageFirst)
		? $pdoPage->makePageLink($url, 1, $tplPageFirst)
		: '',
	'prev' => $page > 1 && !empty($tplPagePrev)
		? $pdoPage->makePageLink($url, $page - 1, $tplPagePrev)
		: '',
	'pages' => '',
	'next' => $page < $pages && !empty($tplPageNext)
		? $pdoPage->makePageLink($url, $page + 1, $tplPageNext)
		: '',
	'last' => $page < $pages - 1 && !empty($tplPageLast)
		? $pdoPage->makePageLink($url, $pages, $tplPageLast)
		: '',
);

for ($i = 1; $i <= $pages; $i++) {
	if ($page == $i && !empty($tplPageActive)) {
		$tpl = $tplPageActive;
	}
	elseif (!empty($tplPage)) {
		$tpl = $tplPage;
	}

	$pagination['pages'] .= !empty($tpl)
		? $pdoPage->makePageLink($url, $i, $tpl)
		: '';
}

foreach (array('first','next','prev','last') as $v) {
	$tpl = 'tplPage'.ucfirst($v);
	var_dump($tpl);
	if (!empty($tpl) && empty($pagination[$v])) {
		$pagination[$v] = $pdoPage->getChunk($tpl);
	}
}

$pagination = !empty($tplPageWrapper)
	? $pdoPage->getChunk($tplPageWrapper, $pagination)
	: $pdoPage->getChunk('', $pagination);

$modx->setPlaceholder($pageNavVar, $pagination);

return $output;