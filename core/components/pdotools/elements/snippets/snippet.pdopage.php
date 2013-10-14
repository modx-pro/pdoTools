<?php
/** @var array $scriptProperties */
/** @var pdoPage $pdoPage */
if (!$modx->loadClass('pdoPage', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoPage = new pdoPage($modx, $scriptProperties);
$pdoPage->addTime('pdoTools loaded');

// Default variables
if (empty($pageVarKey)) {$pageVarKey = 'page';}
if (empty($pageNavVar)) {$pageNavVar = 'page.nav';}
if (empty($totalVar)) {$totalVar = 'total';}
if (empty($page)) {$page = 1;}
if (empty($scheme)) {$scheme = -1;} elseif (is_numeric($scheme)) {$scheme = (integer) $scheme;}
if (empty($pageLimit)) {$pageLimit = 5;} else {$pageLimit = (integer) $pageLimit;}
if (!isset($plPrefix)) {$plPrefix = '';}

// Convert parameters from GetPage if exists
if (!empty($namespace)) {$plPrefix = $namespace;}
if (!empty($pageNavTpl)) {$scriptProperties['tplPage'] = $pageNavTpl;}
if (!empty($pageNavOuterTpl)) {$scriptProperties['tplPageWrapper'] = $pageNavOuterTpl;}
if (!empty($pageActiveTpl)) {$scriptProperties['tplPageActive'] = $pageActiveTpl;}
if (!empty($pageFirstTpl)) {$scriptProperties['tplPageFirst'] = $pageFirstTpl;}
if (!empty($pagePrevTpl)) {$scriptProperties['tplPagePrev'] = $pagePrevTpl;}
if (!empty($pageNextTpl)) {$scriptProperties['tplPageNext'] = $pageNextTpl;}
if (!empty($pageLastTpl)) {$scriptProperties['tplPageLast'] = $pageLastTpl;}
if (!empty($pageSkipTpl)) {$scriptProperties['tplPageSkip'] = $pageSkipTpl;}
if (!empty($pageNavScheme)) {$scriptProperties['scheme'] = $pageNavScheme;}
if (!empty($cache_expires)) {$scriptProperties['cacheTime'] = $cache_expires;}
//---


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

$url = $pdoPage->getBaseUrl();
$output = $pagination = $total = '';

if ($cached = $pdoPage->getCache($page)) {
	extract($cached);
	$pagination = $cached[$pageNavVar];
	$total = $cached[$totalVar];
}
else {
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
		return '';
	}
	/** Pagination */
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
		'pages' => $pageLimit >= 7 && empty($disableModernPagination)
			? $pdoPage->buildModernPagination($page, $pages, $url)
			: $pdoPage->buildClassicPagination($page, $pages, $url),
		'next' => $page < $pages && !empty($tplPageNext)
			? $pdoPage->makePageLink($url, $page + 1, $tplPageNext)
			: '',
		'last' => $page < $pages - 1 && !empty($tplPageLast)
			? $pdoPage->makePageLink($url, $pages, $tplPageLast)
			: '',
	);

	foreach (array('first','prev','next','last') as $v) {
		$tpl = 'tplPage'.ucfirst($v).'Empty';
		if (!empty(${$tpl}) && empty($pagination[$v])) {
			$pagination[$v] = $pdoPage->getChunk(${$tpl});
		}
	}

	$pagination = !empty($tplPageWrapper)
		? $pdoPage->getChunk($tplPageWrapper, $pagination)
		: $pdoPage->parseChunk('', $pagination);
}

$data = array(
	'scriptProperties' => $scriptProperties,
	'output' => $output,
	$pageNavVar => $pagination,
	$totalVar => $total,
);

$pdoPage->setCache($page, $data);
$modx->setPlaceholders($data, $plPrefix);

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}

return $output;