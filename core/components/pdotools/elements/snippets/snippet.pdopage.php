<?php

use ModxPro\PdoTools\Support\Paginator;
use MODX\Revolution\modSnippet;

/** @var array $scriptProperties */
/** @var modX $modx */

// Default variables
if (empty($pageVarKey)) {
    $pageVarKey = 'page';
}
if (empty($pageNavVar)) {
    $pageNavVar = 'page.nav';
}
if (empty($pageCountVar)) {
    $pageCountVar = 'pageCount';
}
if (empty($totalVar)) {
    $totalVar = 'total';
}
if (empty($page)) {
    $page = 1;
}
if (empty($pageLimit)) {
    $pageLimit = 5;
} else {
    $pageLimit = (integer)$pageLimit;
}
if (!isset($plPrefix)) {
    $plPrefix = '';
}
if (!empty($scriptProperties['ajaxMode'])) {
    $scriptProperties['ajax'] = 1;
}

// Convert parameters from getPage if exists
if (!empty($namespace)) {
    $plPrefix = $namespace;
}
if (!empty($pageNavTpl)) {
    $scriptProperties['tplPage'] = $pageNavTpl;
}
if (!empty($pageNavOuterTpl)) {
    $scriptProperties['tplPageWrapper'] = $pageNavOuterTpl;
}
if (!empty($pageActiveTpl)) {
    $scriptProperties['tplPageActive'] = $pageActiveTpl;
}
if (!empty($pageFirstTpl)) {
    $scriptProperties['tplPageFirst'] = $pageFirstTpl;
}
if (!empty($pagePrevTpl)) {
    $scriptProperties['tplPagePrev'] = $pagePrevTpl;
}
if (!empty($pageNextTpl)) {
    $scriptProperties['tplPageNext'] = $pageNextTpl;
}
if (!empty($pageLastTpl)) {
    $scriptProperties['tplPageLast'] = $pageLastTpl;
}
if (!empty($pageSkipTpl)) {
    $scriptProperties['tplPageSkip'] = $pageSkipTpl;
}
if (!empty($pageNavScheme)) {
    $scriptProperties['scheme'] = $pageNavScheme;
}
if (!empty($cache_expires)) {
    $scriptProperties['cacheTime'] = $cache_expires;
}
//---
$strictMode = !empty($strictMode);

$isAjax = !empty($scriptProperties['ajax']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
if ($isAjax && !isset($_REQUEST[$pageVarKey])) {
    return;
}

$modx->services['pdotools_config'] = $scriptProperties;
/** @var Paginator $paginator */
$paginator = $modx->services->get(Paginator::class);
$paginator->pdoTools->addTime('pdoTools loaded');

// Script and styles
if (!$isAjax && !empty($scriptProperties['ajaxMode'])) {
    $paginator->loadJsCss();
}
// Removing of default scripts and styles so they do not overwrote nested snippet parameters
if ($snippet = $modx->getObject(modSnippet::class, ['name' => 'pdoPage'])) {
    $properties = $snippet->get('properties');
    if ($scriptProperties['frontend_js'] == $properties['frontend_js']['value']) {
        unset($scriptProperties['frontend_js']);
    }
    if ($scriptProperties['frontend_css'] == $properties['frontend_css']['value']) {
        unset($scriptProperties['frontend_css']);
    }
}

// Page
if (isset($_REQUEST[$pageVarKey]) && $strictMode && (!is_numeric($_REQUEST[$pageVarKey]) || ($_REQUEST[$pageVarKey] <= 1 && !$isAjax))) {
    return $paginator->redirectToFirst($isAjax);
} elseif (!empty($_REQUEST[$pageVarKey])) {
    $page = (integer)$_REQUEST[$pageVarKey];
}
$scriptProperties['page'] = $page;
$scriptProperties['request'] = $_REQUEST;
$scriptProperties['setTotal'] = true;
// Limit
if (isset($_REQUEST['limit'])) {
    if (is_numeric($_REQUEST['limit']) && abs($_REQUEST['limit']) > 0) {
        $scriptProperties['limit'] = abs($_REQUEST['limit']);
    } elseif ($strictMode) {
        unset($_GET['limit']);

        return $paginator->redirectToFirst($isAjax);
    }
}
if (!empty($maxLimit) && !empty($scriptProperties['limit']) && $scriptProperties['limit'] > $maxLimit) {
    $scriptProperties['limit'] = $maxLimit;
}

// Offset
$_offset = !empty($scriptProperties['offset']) && $scriptProperties['offset'] > 0
    ? (int)$scriptProperties['offset']
    : 0;
$scriptProperties['offset'] = $page > 1
    ? $scriptProperties['limit'] * ($page - 1) + $_offset
    : $_offset;
if (!empty($scriptProperties['offset']) && empty($scriptProperties['limit'])) {
    $scriptProperties['limit'] = 10000000;
}

$cache = !empty($cache) || (!$modx->user->id && !empty($cacheAnonymous));
$charset = $modx->getOption('modx_charset', null, 'UTF-8');
$url = htmlentities($paginator->getBaseUrl(), ENT_QUOTES, $charset);
$output = $pagination = $total = $pageCount = '';

$data = $cache
    ? $paginator->pdoTools->getCache($scriptProperties)
    : [];

if (empty($data)) {
    $output = $paginator->pdoTools->runSnippet('!' . $scriptProperties['element'], $scriptProperties);
    if ($output === false) {
        return '';
    } elseif (!empty($toPlaceholder)) {
        $output = $modx->getPlaceholder($toPlaceholder);
    }

    // Pagination
    $total = (int)$modx->getPlaceholder($totalVar);
    $pageCount = !empty($scriptProperties['limit']) && $total > $_offset
        ? ceil(($total - $_offset) / $scriptProperties['limit'])
        : 0;

    // Redirect to start if somebody specified incorrect page
    if ($page > 1 && $page > $pageCount && $strictMode) {
        return $paginator->redirectToFirst($isAjax);
    }
    if (!empty($pageCount) && $pageCount > 1) {
        $pagination = [
            'first' => $page > 1 && !empty($tplPageFirst)
                ? $paginator->makePageLink($url, 1, $tplPageFirst)
                : '',
            'prev' => $page > 1 && !empty($tplPagePrev)
                ? $paginator->makePageLink($url, $page - 1, $tplPagePrev)
                : '',
            'pages' => $pageLimit >= 7 && empty($disableModernPagination)
                ? $paginator->buildModernPagination($page, $pageCount, $url)
                : $paginator->buildClassicPagination($page, $pageCount, $url),
            'next' => $page < $pageCount && !empty($tplPageNext)
                ? $paginator->makePageLink($url, $page + 1, $tplPageNext)
                : '',
            'last' => $page < $pageCount && !empty($tplPageLast)
                ? $paginator->makePageLink($url, $pageCount, $tplPageLast)
                : '',
        ];

        if (!empty($pageCount)) {
            foreach (['first', 'prev', 'next', 'last'] as $v) {
                $_tpl = 'tplPage' . ucfirst($v) . 'Empty';
                if (!empty(${$_tpl}) && empty($pagination[$v])) {
                    $pagination[$v] = $paginator->pdoTools->getChunk(${$_tpl});
                }
            }
        }
    } else {
        $pagination = [
            'first' => '',
            'prev' => '',
            'pages' => '',
            'next' => '',
            'last' => '',
        ];
    }

    $data = [
        'output' => $output,
        $pageVarKey => $page,
        $pageCountVar => $pageCount,
        $pageNavVar => !empty($tplPageWrapper)
            ? $paginator->pdoTools->getChunk($tplPageWrapper, $pagination)
            : $paginator->pdoTools->parseChunk('', $pagination),
        $totalVar => $total,
    ];
    if ($cache) {
        $paginator->pdoTools->setCache($data, $scriptProperties);
    }
}
/** @var bool $showLog */
if ($modx->user->isAuthenticated('mgr') && (bool)$showLog) {
    $modx->setPlaceholder('pdoPageLog', print_r($paginator->pdoTools->getTime(), true));
}

if ($isAjax) {
    if ($pageNavVar !== 'pagination') {
        $data['pagination'] = $data[$pageNavVar];
        unset($data[$pageNavVar]);
    }
    if ($pageCountVar !== 'pages') {
        $data['pages'] = (int)$data[$pageCountVar];
        unset($data[$pageCountVar]);
    }
    if ($pageVarKey !== 'page') {
        $data['page'] = (int)$data[$pageVarKey];
        unset($data[$pageVarKey]);
    }
    if ($totalVar !== 'total') {
        $data['total'] = (int)$data[$totalVar];
        unset($data[$totalVar]);
    }

    $maxIterations = (integer)$modx->getOption('parser_max_iterations', null, 10);
    $modx->getParser()->processElementTags('', $data['output'], false, false, '[[', ']]', [], $maxIterations);
    $modx->getParser()->processElementTags('', $data['output'], true, true, '[[', ']]', [], $maxIterations);

    @session_write_close();
    exit(json_encode($data));
}

if (!empty($setMeta)) {
    $canurl = $paginator->pdoTools->config('scheme') !== 'full'
        ? $paginator->getCanonicalUrl($url)
        : $url;
    $modx->regClientStartupHTMLBlock('<link rel="canonical" href="' . $canurl . '"/>');
    if ($data[$pageVarKey] > 1) {
        $prevUrl = $paginator->makePageLink($canurl, $data[$pageVarKey] - 1);
        $modx->regClientStartupHTMLBlock(
            '<link rel="prev" href="' . $prevUrl . '"/>'
        );
    }
    if ($data[$pageVarKey] < $data[$pageCountVar]) {
        $nextUrl = $paginator->makePageLink($canurl, $data[$pageVarKey] + 1);
        $modx->regClientStartupHTMLBlock(
            '<link rel="next" href="' . $nextUrl . '"/>'
        );
    }
}

$modx->setPlaceholders($data, $plPrefix);
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $data['output']);
} else {
    return $data['output'];
}
