<?php
/** @var array $scriptProperties */
/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded');

if (empty($id)) {
    $id = $modx->resource->id;
}
if (empty($limit)) {
    $limit = 1;
}
if (!isset($outputSeparator)) {
    $outputSeparator = "\n";
}
$fastMode = !empty($fastMode);

$class = 'modResource';
$resource = ($id == $modx->resource->id)
    ? $modx->resource
    : $modx->getObject($class, $id);
if (!$resource) {
    return '';
}

// We need to determine ids of neighbors
$params = $scriptProperties;
$params['select'] = 'id';
$params['limit'] = 0;
if (!empty($parents)) {
    $parents = array_map('trim', explode(',', $parents));
    if (!in_array($resource->parent, $parents)) {
        $parents[] = $resource->parent;
    }
    $key = array_search($resource->parent * -1, $parents);
    if ($key !== false) {
        unset($parents[$key]);
    }
    $params['parents'] = implode(',', $parents);
    $ids = $pdoFetch->getCollection('modResource', array(), $params);
    unset($scriptProperties['parents']);
} else {
    $ids = $pdoFetch->getCollection('modResource', array('parent' => $resource->parent), $params);
}

$found = false;
$prev = $next = array();
foreach ($ids as $v) {
    if ($v['id'] == $id) {
        $found = true;
        continue;
    } elseif (!$found) {
        $prev[] = $v['id'];
    } else {
        $next[] = $v['id'];
        if (count($next) >= $limit) {
            break;
        }
    }
}
$prev = array_splice($prev, $limit * -1);
if (!empty($loop)) {
    if (!count($prev)) {
        $v = end($ids);
        $prev[] = $v['id'];
    } else {
        if (!count($next)) {
            $v = reset($ids);
            $next[] = $v['id'];
        }
    }
}
$ids = array_merge($prev, $next, array($resource->parent));
$pdoFetch->addTime('Found ids of neighbors: ' . implode(',', $ids));

// Query conditions
$where = array($class . '.id:IN' => $ids);

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
if (empty($includeContent) && empty($useWeblinkUrl)) {
    $key = array_search('content', $resourceColumns);
    unset($resourceColumns[$key]);
}
$select = array($class => implode(',', $resourceColumns));

// Add custom parameters
foreach (array('where', 'select') as $v) {
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
$default = array(
    'class' => $class,
    'where' => json_encode($where),
    'select' => json_encode($select),
    //'groupby' => $class.'.id',
    'sortby' => $class . '.menuindex',
    'sortdir' => 'ASC',
    'return' => 'data',
    'limit' => 0,
    'totalVar' => 'pdoneighbors.total',
);

// Merge all properties and run!
unset($scriptProperties['limit']);
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);

$rows = $pdoFetch->run();
$prev = array_flip($prev);
$next = array_flip($next);

$output = array('prev' => array(), 'up' => array(), 'next' => array());
foreach ($rows as $row) {
    if (empty($row['menutitle'])) {
        $row['menutitle'] = $row['pagetitle'];
    }
    if (!empty($useWeblinkUrl) && $row['class_key'] == 'modWebLink') {
        $row['link'] = is_numeric(trim($row['content'], '[]~ '))
            ? $pdoFetch->makeUrl(intval(trim($row['content'], '[]~ ')), $row)
            : $row['content'];
    } else {
        $row['link'] = $pdoFetch->makeUrl($row['id'], $row);
    }

    if (isset($prev[$row['id']])) {
        $output['prev'][] = !empty($tplPrev)
            ? $pdoFetch->getChunk($tplPrev, $row, $fastMode)
            : $pdoFetch->getChunk('', $row);
    } elseif (isset($next[$row['id']])) {
        $output['next'][] = !empty($tplNext)
            ? $pdoFetch->getChunk($tplNext, $row, $fastMode)
            : $pdoFetch->getChunk('', $row);
    } else {
        $output['up'][] = !empty($tplUp)
            ? $pdoFetch->getChunk($tplUp, $row, $fastMode)
            : $pdoFetch->getChunk('', $row);
    }
}
$pdoFetch->addTime('Chunks processed');

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $log .= '<pre class="pdoNeighborsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

foreach ($output as &$row) {
    $row = implode($outputSeparator, $row);
}

if (!empty($toSeparatePlaceholders)) {
    $output['log'] = $log;
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    if (!empty($rows) || !empty($wrapIfEmpty)) {
        $output = !empty($tplWrapper)
            ? $pdoFetch->getChunk($tplWrapper, $output, $fastMode)
            : $pdoFetch->getChunk('', $output);
    } else {
        $output = '';
    }
    $output .= $log;

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}
