<?php

use ModxPro\PdoTools\Fetch;
use MODX\Revolution\modResource;
use MODX\Revolution\modWebLink;

/** @var array $scriptProperties */
/** @var \MODX\Revolution\modX $modx */


$modx->services['pdotools_config'] = $scriptProperties;
$pdoFetch = $modx->services->get(Fetch::class);

$pdoFetch->addTime('pdoTools loaded');

if (!isset($from) || $from == '') {
    $from = 0;
}
if (empty($to)) {
    $to = $modx->resource->id;
}
if (empty($direction)) {
    $direction = 'ltr';
}
if ($outputSeparator == '&nbsp;&rarr;&nbsp;' && $direction == 'rtl') {
    $outputSeparator = '&nbsp;&larr;&nbsp;';
}
$limit = $limit ?: 10;

// For compatibility with BreadCrumb
if (!empty($maxCrumbs)) {
    $limit = $maxCrumbs;
}
if (!empty($containerTpl)) {
    $tplWrapper = $containerTpl;
}
if (!empty($currentCrumbTpl)) {
    $tplCurrent = $currentCrumbTpl;
}
if (!empty($linkCrumbTpl)) {
    $scriptProperties['tpl'] = $linkCrumbTpl;
}
if (!empty($maxCrumbTpl)) {
    $tplMax = $maxCrumbTpl;
}
if (isset($showBreadCrumbsAtHome)) {
    $showAtHome = $showBreadCrumbsAtHome;
}
if (isset($showHomeCrumb)) {
    $showHome = $showHomeCrumb;
}
if (isset($showCurrentCrumb)) {
    $showCurrent = $showCurrentCrumb;
}
// --
$fastMode = !empty($fastMode);
$siteStart = (int)$modx->getOption('siteStart', $scriptProperties, $modx->getOption('site_start'));

if (empty($showAtHome) && $modx->resource->id === $siteStart) {
    return '';
}

$class = modResource::class;
$alias = $modx->getAlias($class);
// Start building "Where" expression
$where = [];
if (empty($showUnpublished) && empty($showUnPub)) {
    $where['published'] = 1;
}
if (empty($showHidden)) {
    $where['hidemenu'] = 0;
}
if (empty($showDeleted)) {
    $where['deleted'] = 0;
}
if (!empty($hideContainers) && empty($showContainer)) {
    $where['isfolder'] = 0;
}

$resource = ($to == $modx->resource->id)
    ? $modx->resource
    : $modx->getObject($class, $to);

if (!$resource) {
    $message = 'Could not build breadcrumbs to resource "' . $to . '"';

    return '';
}

if (!empty($customParents)) {
    $customParents = is_array($customParents) ? $customParents : array_map('trim', explode(',', $customParents));
    $parents = is_array($customParents) ? array_reverse($customParents) : [];
}
if (empty($parents)) {
    $parents = $modx->getParentIds($resource->id, $limit, ['context' => $resource->get('context_key')]);
}
if (!empty($showHome)) {
    $parents[] = $siteStart;
}

$ids = [$resource->id];
foreach ($parents as $parent) {
    if (!empty($parent)) {
        $ids[] = $parent;
    }
    if (!empty($from) && $parent == $from) {
        break;
    }
}
$where['id:IN'] = $ids;

if (!empty($exclude)) {
    $where['id:NOT IN'] = array_map('trim', explode(',', $exclude));
}

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
$select = [$alias => implode(',', $resourceColumns)];

// Add custom parameters
foreach (['where', 'select'] as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

// Default parameters
$default = [
    'class' => $class,
    'where' => $where,
    'select' => $select,
    'groupby' => $alias . '.id',
    'sortby' => "find_in_set(`$alias`.`id`,'" . implode(',', $ids) . "')",
    'sortdir' => '',
    'return' => 'data',
    'totalVar' => 'pdocrumbs.total',
    'disableConditions' => true,
];

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$rows = $pdoFetch->run();

$output = [];
if (!empty($rows) && is_array($rows)) {
    if (strtolower($direction) == 'ltr') {
        $rows = array_reverse($rows);
    }

    foreach ($rows as $row) {
        if (!empty($useWeblinkUrl) && $row['class_key'] === modWebLink::class) {
            $row['link'] = is_numeric(trim($row['content'], '[]~ '))
                ? $pdoFetch->makeUrl((int)trim($row['content'], '[]~ '), $row)
                : $row['content'];
        } else {
            $row['link'] = $pdoFetch->makeUrl($row['id'], $row);
        }

        $row = array_merge(
            $scriptProperties,
            $row,
            ['idx' => $pdoFetch->idx++]
        );
        if (empty($row['menutitle'])) {
            $row['menutitle'] = $row['pagetitle'];
        }

        if (isset($return) && $return === 'data') {
            $output[] = $row;
            continue;
        }
        if ($row['id'] == $resource->id && empty($showCurrent)) {
            continue;
        } elseif ($row['id'] == $resource->id && !empty($tplCurrent)) {
            $tpl = $tplCurrent;
        } elseif ($row['id'] == $siteStart && !empty($tplHome)) {
            $tpl = $tplHome;
        } else {
            $tpl = $pdoFetch->defineChunk($row);
        }
        $output[] = empty($tpl)
            ? '<pre>' . $pdoFetch->getChunk('', $row) . '</pre>'
            : $pdoFetch->getChunk($tpl, $row, $fastMode);
    }
}
if (isset($return) && $return === 'data') {
    return $output;
}

$pdoFetch->addTime('Chunks processed');

if (count($output) == 1 && !empty($hideSingle)) {
    $pdoFetch->addTime('The only result was hidden, because the parameter "hideSingle" activated');
    $output = [];
}

$log = '';
if ($modx->user->isAuthenticated('mgr') && (bool)$showLog) {
    $modx->setPlaceholder('pdoCrumbsLog', print_r($pdoFetch->getTime(), true));
}

if (!empty($toSeparatePlaceholders)) {
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    $output = implode($outputSeparator, $output);
    if ($pdoFetch->idx >= $limit && !empty($tplMax) && !empty($output)) {
        $output = ($direction == 'ltr')
            ? $pdoFetch->getChunk($tplMax, [], $fastMode) . $output
            : $output . $pdoFetch->getChunk($tplMax, [], $fastMode);
    }
    $output .= $log;

    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, ['output' => $output, 'crumbs' => $output], $fastMode);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}
