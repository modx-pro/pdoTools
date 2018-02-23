<?php
/** @var array $scriptProperties */

use MODX\Components\PDOTools\Fetch;
use MODX\Components\PDOTools\Helpers\Functions;
use MODX\Components\PDOTools\Snippet\Menu;
use MODX\Revolution\modResource;

// Convert parameters from Wayfinder if exists
if (isset($startId)) {
    $scriptProperties['parents'] = $startId;
}
if (!empty($includeDocs)) {
    $tmp = array_map('trim', explode(',', $includeDocs));
    foreach ($tmp as $v) {
        if (!empty($scriptProperties['resources'])) {
            $scriptProperties['resources'] .= ',' . $v;
        } else {
            $scriptProperties['resources'] = $v;
        }
    }
}
if (!empty($excludeDocs)) {
    $tmp = array_map('trim', explode(',', $excludeDocs));
    foreach ($tmp as $v) {
        if (!empty($scriptProperties['resources'])) {
            $scriptProperties['resources'] .= ',-' . $v;
        } else {
            $scriptProperties['resources'] = '-' . $v;
        }
    }
}

if (!empty($previewUnpublished) && $modx->hasPermission('view_unpublished')) {
    $scriptProperties['showUnpublished'] = 1;
}

$scriptProperties['depth'] = empty($level) ? 100 : abs($level) - 1;
if (!empty($contexts)) {
    $scriptProperties['context'] = $contexts;
}
if (empty($scriptProperties['context'])) {
    $scriptProperties['context'] = $modx->resource->context_key;
}

// Save original parents specified by user
$specified_parents = array_map('trim', explode(',', $scriptProperties['parents']));

if ($scriptProperties['parents'] === '') {
    $scriptProperties['parents'] = $modx->resource->id;
} elseif ($scriptProperties['parents'] === 0 || $scriptProperties['parents'] === '0') {
    if ($scriptProperties['depth'] !== '' && $scriptProperties['depth'] !== 100) {
        $contexts = array_map('trim', explode(',', $scriptProperties['context']));
        $parents = [];
        if (!empty($scriptProperties['showDeleted'])) {
            /** @var Fetch $pdoFetch */
            $pdoFetch = $modx->services->get('pdofetch');
            foreach ($contexts as $ctx) {
                $parents = array_merge($parents, $pdoFetch->getChildIds(modResource::class, 0, $scriptProperties['depth'], ['context' => $ctx]));
            }
        } else {
            foreach ($contexts as $ctx) {
                $parents = array_merge($parents,
                    $modx->getChildIds(0, $scriptProperties['depth'], ['context' => $ctx]));
            }
        }
        $scriptProperties['parents'] = !empty($parents) ? implode(',', $parents) : '+0';
        $scriptProperties['depth'] = 0;
    }
    $scriptProperties['includeParents'] = 1;
    $scriptProperties['displayStart'] = 0;
} else {
    $parents = array_map('trim', explode(',', $scriptProperties['parents']));
    $parents_in = $parents_out = [];
    foreach ($parents as $v) {
        if (!is_numeric($v)) {
            continue;
        }
        if ($v[0] == '-') {
            $parents_out[] = abs($v);
        } else {
            $parents_in[] = abs($v);
        }
    }

    if (empty($parents_in)) {
        $scriptProperties['includeParents'] = 1;
        $scriptProperties['displayStart'] = 0;
    }
}

if (!empty($displayStart)) {
    $scriptProperties['includeParents'] = 1;
}
if (!empty($ph)) {
    $toPlaceholder = $ph;
}
if (!empty($sortOrder)) {
    $scriptProperties['sortdir'] = $sortOrder;
}
if (!empty($sortBy)) {
    $scriptProperties['sortby'] = $sortBy;
}
if (!empty($permissions)) {
    $scriptProperties['checkPermissions'] = $permissions;
}
if (!empty($cacheResults)) {
    $scriptProperties['cache'] = $cacheResults;
}
if (!empty($ignoreHidden)) {
    $scriptProperties['showHidden'] = $ignoreHidden;
}

$wfTemplates = [
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
    'startItemTpl' => 'tplStart',
];
foreach ($wfTemplates as $k => $v) {
    if (isset(${$k})) {
        $scriptProperties[$v] = ${$k};
    }
}
//---

/** @var Menu $pdoMenu */
$pdoClass = $modx->services->has('pdomenu')
    ? get_class($modx->services->get('pdomenu'))
    : Menu::class;
$pdoMenu = new $pdoClass($modx, $scriptProperties);
$pdoMenu->pdoTools->addTime('pdoTools loaded');

$cache = !empty($cache) || (!$modx->user->id && !empty($cacheAnonymous));
if (empty($scriptProperties['cache_key'])) {
    $scriptProperties['cache_key'] = 'pdomenu/' . sha1(serialize($scriptProperties));
}

$output = '';
$tree = [];
if ($cache) {
    $tree = $pdoMenu->pdoTools->cache->get($scriptProperties);
}
if (empty($tree)) {
    $data = $pdoMenu->pdoTools->run();
    $data = Functions::buildTree($data, 'id', 'parent', $specified_parents);
    $tree = [];
    foreach ($data as $k => $v) {
        if (empty($v['id'])) {
            if (!in_array($k, $specified_parents) && !$pdoMenu->checkResource($k)) {
                continue;
            } else {
                $tree = array_merge($tree, $v['children']);
            }
        } else {
            $tree[$k] = $v;
        }
    }
    if ($cache) {
        $pdoMenu->pdoTools->cache->set($tree, $scriptProperties);
    }
}
if (!empty($tree)) {
    $output = $pdoMenu->templateTree($tree);
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="pdoMenuLog">' . print_r($pdoMenu->pdoTools->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}