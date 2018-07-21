<?php
/** @var array $scriptProperties */
if (!empty($input)) {
    $id = $input;
}
if (!isset($default)) {
    $default = '';
}
if (!isset($output)) {
    $output = '';
}
$class = $modx->getOption('class', $scriptProperties, 'modResource', true);
$isResource = $class == 'modResource' || in_array($class, $modx->getDescendants('modResource'));

if (empty($field)) {
    $field = 'pagetitle';
}
$top = isset($top) ? intval($top) : 0;
$topLevel = isset($topLevel) ? intval($topLevel) : 0;
if (!empty($options)) {
    $options = trim($options);
    if ($options[0] == '{') {
        $tmp = json_decode($options, true);
        if (is_array($tmp)) {
            extract($tmp);
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
        return 'You must specify an id of ' . $class;
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
            $pids = $modx->getChildIds(0, $topLevel, array('context' => $context));
            $pid = $id;
            while (true) {
                $tmp = $modx->getParentIds($pid, 1, array('context' => $context));
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
                $tmp = $modx->getParentIds($pid, 1, array('context' => $context));
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
        $pids = $modx->getParentIds($id, 10, array('context' => $context));
        if (!$topLevel || count($pids) >= $topLevel) {
            while ($parentIds = $modx->getParentIds($id, 1, array('context' => $context))) {
                $pid = array_pop($parentIds);
                if ($pid == $top) {
                    break;
                }
                $id = $pid;
                $parentIds = $modx->getParentIds($id, 10, array('context' => $context));
                if ($topLevel && count($parentIds) < $topLevel) {
                    break;
                }
            }
        }
    }
}

/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded');

$where = array($class . '.id' => $id);
// Add custom parameters
foreach (array('where') as $v) {
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
    $scriptProperties['select'] = array($class => $field);
    $scriptProperties['includeTVs'] = '';
} elseif ($isResource) {
    $scriptProperties['select'] = array($class => 'id');
    $scriptProperties['includeTVs'] = $field;
}
// Additional default field
if (!empty($default)) {
    $default = strtolower($default);
    if (in_array($default, $resourceColumns)) {
        $scriptProperties['select'][$class] .= ',' . $default;
    } elseif ($isResource) {
        $scriptProperties['includeTVs'] = empty($scriptProperties['includeTVs'])
            ? $default
            : $scriptProperties['includeTVs'] . ',' . $default;
    }
}

$scriptProperties['disableConditions'] = true;
if ($row = $pdoFetch->getObject($class, $where, $scriptProperties)) {
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