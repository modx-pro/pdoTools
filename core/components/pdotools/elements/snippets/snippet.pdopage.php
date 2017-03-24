<?php
/** @var array $scriptProperties */
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

/** @var pdoPage $pdoPage */
$fqn = $modx->getOption('pdoPage.class', null, 'pdotools.pdopage', true);
$path = $modx->getOption('pdopage_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoPage = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoPage->pdoTools->addTime('pdoTools loaded');

// Script and styles
if (!$isAjax && !empty($scriptProperties['ajaxMode'])) {
    $pdoPage->loadJsCss();
}
// Removing of default scripts and styles so they do not overwrote nested snippet parameters
if ($snippet = $modx->getObject('modSnippet', array('name' => 'pdoPage'))) {
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
    return $pdoPage->redirectToFirst($isAjax);
} elseif (!empty($_REQUEST[$pageVarKey])) {
    $page = (integer)$_REQUEST[$pageVarKey];
}
$scriptProperties['page'] = $page;
$scriptProperties['request'] = $_REQUEST;

// Limit
if (isset($_REQUEST['limit'])) {
    if (is_numeric($_REQUEST['limit']) && abs($_REQUEST['limit']) > 0) {
        $scriptProperties['limit'] = abs($_REQUEST['limit']);
    } elseif ($strictMode) {
        unset($_GET['limit']);

        return $pdoPage->redirectToFirst($isAjax);
    }
}
if (!empty($maxLimit) && !empty($scriptProperties['limit']) && $scriptProperties['limit'] > $maxLimit) {
    $scriptProperties['limit'] = $maxLimit;
}

// Offset
$offset = !empty($scriptProperties['offset']) && $scriptProperties['offset'] > 0
    ? (int)$scriptProperties['offset']
    : 0;
$scriptProperties['offset'] = $page > 1
    ? $scriptProperties['limit'] * ($page - 1) + $offset
    : $offset;
if (!empty($scriptProperties['offset']) && empty($scriptProperties['limit'])) {
    $scriptProperties['limit'] = 10000000;
}

$cache = !empty($cache) || (!$modx->user->id && !empty($cacheAnonymous));
$url = $pdoPage->getBaseUrl();
$output = $pagination = $total = $pageCount = '';

$data = $cache
    ? $pdoPage->pdoTools->getCache($scriptProperties)
    : array();

if (empty($data)) {
    $output = $pdoPage->pdoTools->runSnippet($scriptProperties['element'], $scriptProperties);
    if ($output === false) {
        return '';
    } elseif (!empty($toPlaceholder)) {
        $output = $modx->getPlaceholder($toPlaceholder);
    }

    /** Pagination */
    $total = (int)$modx->getPlaceholder($totalVar);
    $pageCount = !empty($scriptProperties['limit']) && $total > $offset
        ? ceil(($total - $offset) / $scriptProperties['limit'])
        : 0;

    // Redirect to start if somebody specified incorrect page
    if ($page > 1 && $page > $pageCount && $strictMode) {
        return $pdoPage->redirectToFirst($isAjax);
    } elseif (!empty($pageCount) && $pageCount > 1) {
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
            foreach (array('first', 'prev', 'next', 'last') as $v) {
                $tpl = 'tplPage' . ucfirst($v) . 'Empty';
                if (!empty(${$tpl}) && empty($pagination[$v])) {
                    $pagination[$v] = $pdoPage->pdoTools->getChunk(${$tpl});
                }
            }
        }

        if (!empty($setMeta) && !$isAjax) {
            $modx->regClientStartupHTMLBlock('<link rel="canonical" href="' . $url . '"/>');
            if ($page > 1) {
                $modx->regClientStartupHTMLBlock('<link rel="prev" href="' . $pdoPage->makePageLink($url,
                        $page - 1) . '"/>');
            }
            if ($page < $pageCount) {
                $modx->regClientStartupHTMLBlock('<link rel="next" href="' . $pdoPage->makePageLink($url,
                        $page + 1) . '"/>');
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
    if ($cache) {
        $pdoPage->pdoTools->setCache($data, $scriptProperties);
    }
}

if ($isAjax) {
    if ($pageNavVar != 'pagination') {
        $data['pagination'] = $data[$pageNavVar];
        unset($data[$pageNavVar]);
    }
    if ($pageCountVar != 'pages') {
        $data['pages'] = (int)$data[$pageCountVar];
        unset($data[$pageCountVar]);
    }
    if ($pageVarKey != 'page') {
        $data['page'] = (int)$data[$pageVarKey];
        unset($data[$pageVarKey]);
    }
    if ($totalVar != 'total') {
        $data['total'] = (int)$data[$totalVar];
        unset($data[$totalVar]);
    }

    $maxIterations = (integer)$modx->getOption('parser_max_iterations', null, 10);
    $modx->getParser()->processElementTags('', $data['output'], false, false, '[[', ']]', array(), $maxIterations);
    $modx->getParser()->processElementTags('', $data['output'], true, true, '[[', ']]', array(), $maxIterations);

    @session_write_close();
    exit(json_encode($data));
} else {
    $modx->setPlaceholders($data, $plPrefix);
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $data['output']);
    } else {
        return $data['output'];
    }
}