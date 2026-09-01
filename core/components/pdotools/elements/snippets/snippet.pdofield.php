<?php

use ModxPro\PdoTools\Fetch;
use MODX\Revolution\modResource;

/** @var array $scriptProperties */
/** @var modX $modx */

if (!empty($input)) {
    $id = $input;
}
if (!isset($default)) {
    $default = '';
}
if (!isset($output)) {
    $output = '';
}
$class = $modx->getOption('class', $scriptProperties, modResource::class, true);
$alias = $modx->getAlias($class);
$isResource = $class === modResource::class || in_array($class, $modx->getDescendants(modResource::class));

if (empty($field)) {
    $field = 'pagetitle';
}
$top = isset($top) ? (int)$top : 0;
$topLevel = isset($topLevel) ? (int)$topLevel : 0;
if (!empty($options) && is_string($options)) {
    $options = trim($options);
    if ($options[0] == '{') {
        $tmp = json_decode($options, true);
        if (is_array($tmp)) {
            extract($tmp, EXTR_OVERWRITE);
            $scriptProperties = array_merge($scriptProperties, $tmp);
        }
    } else {
        $field = $options;
    }
}
if (empty($id)) {
    if (!empty($modx->resource)) {
        $id = $modx->resource->id;
    } else {
        return 'You must specify an id of ' . $alias;
    }
}
if (!isset($context)) {
    $context = '';
}

// Gets the parent from root of context, if specified
if ($isResource && $id && ($top || $topLevel)) {
    // Select needed context for parents functionality
    if (empty($context)) {
        $q = $modx->newQuery($class, $id);
        $q->select('context_key');
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $modx->queryTime += microtime(true) - $tstart;
            $modx->executedQueries++;
            $context = $q->stmt->fetch(PDO::FETCH_COLUMN);
        }
    }
    // Original pdoField logic
    if (empty($ultimate)) {
        if ($topLevel) {
            $pids = $modx->getChildIds(0, $topLevel, ['context' => $context]);
            $pid = $id;
            while (true) {
                $tmp = $modx->getParentIds($pid, 1, ['context' => $context]);
                if (!$pid = current($tmp)) {
                    break;
                } elseif (in_array($pid, $pids)) {
                    $id = $pid;
                    break;
                }
            }
        } elseif ($top) {
            $pid = $id;
            for ($i = 1; $i <= $top; $i++) {
                $tmp = $modx->getParentIds($pid, 1, ['context' => $context]);
                if (!$pid = current($tmp)) {
                    break;
                }
                $id = $pid;
            }
        }
    }
    // UltimateParent logic
    // https://github.com/splittingred/UltimateParent/blob/develop/core/components/ultimateparent/snippet.ultimateparent.php
    elseif ($id != $top) {
        $pid = $id;
        $pids = $modx->getParentIds($id, 10, ['context' => $context]);
        if (!$topLevel || count($pids) >= $topLevel) {
            while ($parentIds = $modx->getParentIds($id, 1, ['context' => $context])) {
                $pid = array_pop($parentIds);
                if ($pid == $top) {
                    break;
                }
                $id = $pid;
                $parentIds = $modx->getParentIds($id, 10, ['context' => $context]);
                if ($topLevel && count($parentIds) < $topLevel) {
                    break;
                }
            }
        }
    }
}

$modx->services['pdotools_config'] = $scriptProperties;
$pdoFetch = $modx->services->get(Fetch::class);
$pdoFetch->addTime('pdoTools loaded');

$where = [$alias . '.id' => $id];
// Add custom parameters
foreach (['where'] as $v) {
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

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
$field = strtolower($field);
if (in_array($field, $resourceColumns)) {
    $scriptProperties['select'] = [$alias => $field];
    $scriptProperties['includeTVs'] = '';
} elseif ($isResource) {
    $scriptProperties['select'] = [$alias => 'id'];
    $scriptProperties['includeTVs'] = $field;
}
// Additional default field
if (!empty($default)) {
    $default = strtolower($default);
    if (in_array($default, $resourceColumns)) {
        $scriptProperties['select'][$alias] .= ',' . $default;
    } elseif ($isResource) {
        $scriptProperties['includeTVs'] = empty($scriptProperties['includeTVs'])
            ? $default
            : $scriptProperties['includeTVs'] . ',' . $default;
    }
}

$scriptProperties['disableConditions'] = true;
if ($row = $pdoFetch->getArray($class, $where, $scriptProperties)) {
    foreach ($row as $k => $v) {
        if (strtolower($k) == $field && $v != '') {
            $output = $v;
            break;
        }
    }

    if (empty($output) && !empty($default)) {
        foreach ($row as $k => $v) {
            if (strtolower($k) == $default && $v != '') {
                $output = $v;
                break;
            }
        }
    }
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}
