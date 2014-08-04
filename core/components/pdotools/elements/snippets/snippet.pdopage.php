<?php
/** @var array $scriptProperties */
/** @var pdoPage $pdoPage */
if (!$modx->loadClass('pdotools.pdoPage', MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoPage from "MODX_CORE_PATH/components/pdotools/model/".');
	return false;
}
$pdoPage = new pdoPage($modx, $scriptProperties);
$pdoPage->pdoTools->addTime('pdoTools loaded');

// Default variables
if (empty($pageVarKey)) {$pageVarKey = 'page';}
if (empty($pageNavVar)) {$pageNavVar = 'page.nav';}
if (empty($pageCountVar)) {$pageCountVar = 'pageCount';}
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
$scriptProperties['page'] = $page;
$scriptProperties['request'] = $_REQUEST;

// Limit
if (isset($_REQUEST['limit'])) {
	if (is_numeric($_REQUEST['limit']) && abs($_REQUEST['limit']) > 0) {
		$scriptProperties['limit'] = abs($_REQUEST['limit']);
	}
	else {
		unset($_GET['limit']);
		return $pdoPage->redirectToFirst();
	}
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
$output = $pagination = $total = $pageCount = '';

$data = !empty($cache) || !$modx->user->id && !empty($cacheAnonymous)
	? $pdoPage->pdoTools->getCache($scriptProperties)
	: array();

if (empty($data)) {
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
	$pageCount = !empty($scriptProperties['limit'])
		? ceil($total / $scriptProperties['limit'])
		: 0;

	// Redirect to start if somebody specified incorrect page
	if ($page > 1 && $page > $pageCount) {
		return $pdoPage->redirectToFirst();
	}
	elseif (!empty($pageCount) && $pageCount > 1) {
		$pagination = array(
			'first' => $page > 1 && !empty($tplPageFirst)
				? $pdoPage->makePageLink($url, 1, $tplPageFirst)
				: '',
			'prev' => $page > 1 && !empty($tplPagePrev)
				? $pdoPage->makePageLink($url, $page - 1, $tplPagePrev)
				: '',
			'pages' => $pageLimit >= 7 && empty($disableModernPagination)
				? $pdoPage->buildModernPagination($page, $pageCount, $url)
				: $pdoPage->buildClassicPagination($page, $pageCount, $url),
			'next' => $page < $pageCount && !empty($tplPageNext)
				? $pdoPage->makePageLink($url, $page + 1, $tplPageNext)
				: '',
			'last' => $page < $pageCount && !empty($tplPageLast)
				? $pdoPage->makePageLink($url, $pageCount, $tplPageLast)
				: '',
		);

		if (!empty($pageCount)) {
			foreach (array('first','prev','next','last') as $v) {
				$tpl = 'tplPage'.ucfirst($v).'Empty';
				if (!empty(${$tpl}) && empty($pagination[$v])) {
					$pagination[$v] = $pdoPage->pdoTools->getChunk(${$tpl});
				}
			}
		}

		$pagination = !empty($tplPageWrapper)
			? $pdoPage->pdoTools->getChunk($tplPageWrapper, $pagination)
			: $pdoPage->pdoTools->parseChunk('', $pagination);
	}

	$data = array(
		'output' => $output,
		$pageVarKey => $page,
		$pageCountVar => $pageCount,
		$pageNavVar => $pagination,
		$totalVar => $total,
	);
	if (!empty($cache) || !$modx->user->id && !empty($cacheAnonymous)) {
		$pdoPage->pdoTools->setCache($data, $scriptProperties);
	}
}

$modx->setPlaceholders($data, $plPrefix);

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $data['output']);
}
else {
	return $data['output'];
}