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

// Default variables
if (empty($tpl)) {
    $tpl = "@INLINE \n<url>\n\t<loc>[[+url]]</loc>\n\t<lastmod>[[+date]]</lastmod>\n\t<changefreq>[[+update]]</changefreq>\n\t<priority>[[+priority]]</priority>\n</url>";
}
if (empty($tplWrapper)) {
    $tplWrapper = "@INLINE <?xml version=\"1.0\" encoding=\"[[++modx_charset]]\"?>\n<urlset xmlns=\"[[+schema]]\">\n[[+output]]\n</urlset>";
}
if (empty($sitemapSchema)) {
    $sitemapSchema = 'http://www.sitemaps.org/schemas/sitemap/0.9';
}
if (empty($outputSeparator)) {
    $outputSeparator = "\n";
}
if (empty($cacheKey)) {
    $scriptProperties['cacheKey'] = 'sitemap/' . substr(md5(json_encode($scriptProperties)), 0, 6);
}

// Convert parameters from GoogleSiteMap if exists
if (!empty($itemTpl)) {
    $tpl = $itemTpl;
}
if (!empty($containerTpl)) {
    $tplWrapper = $containerTpl;
}
if (!empty($allowedtemplates)) {
    $scriptProperties['templates'] = $allowedtemplates;
}
if (!empty($maxDepth)) {
    $scriptProperties['depth'] = $maxDepth;
}
if (isset($hideDeleted)) {
    $scriptProperties['showDeleted'] = !$hideDeleted;
}
if (isset($published)) {
    $scriptProperties['showUnpublished'] = !$published;
}
if (isset($searchable)) {
    $scriptProperties['showUnsearchable'] = !$searchable;
}
if (!empty($googleSchema)) {
    $sitemapSchema = $googleSchema;
}
if (!empty($excludeResources)) {
    $tmp = array_map('trim', explode(',', $excludeResources));
    foreach ($tmp as $v) {
        if (!empty($scriptProperties['resources'])) {
            $scriptProperties['resources'] .= ',-' . $v;
        } else {
            $scriptProperties['resources'] = '-' . $v;
        }
    }
}
if (!empty($excludeChildrenOf)) {
    $tmp = array_map('trim', explode(',', $excludeChildrenOf));
    foreach ($tmp as $v) {
        if (!empty($scriptProperties['parents'])) {
            $scriptProperties['parents'] .= ',-' . $v;
        } else {
            $scriptProperties['parents'] = '-' . $v;
        }
    }
}
if (!empty($startId)) {
    if (!empty($scriptProperties['parents'])) {
        $scriptProperties['parents'] .= ',' . $startId;
    } else {
        $scriptProperties['parents'] = $startId;
    }
}
if (!empty($sortBy)) {
    $scriptProperties['sortby'] = $sortBy;
}
if (!empty($sortDir)) {
    $scriptProperties['sortdir'] = $sortDir;
}
if (!empty($priorityTV)) {
    if (!empty($scriptProperties['includeTVs'])) {
        $scriptProperties['includeTVs'] .= ',' . $priorityTV;
    } else {
        $scriptProperties['includeTVs'] = $priorityTV;
    }
}
if (!empty($itemSeparator)) {
    $outputSeparator = $itemSeparator;
}
//---


$class = 'modResource';
$where = array();
if (empty($showHidden)) {
    $where[] = array(
        $class . '.hidemenu' => 0,
        'OR:' . $class . '.class_key:IN' => array('Ticket', 'Article'),
    );
}
if (empty($context)) {
    $scriptProperties['context'] = $modx->context->key;
}

$select = array($class => 'id,editedon,createdon,context_key,class_key,uri');
if (!empty($useWeblinkUrl)) {
    $select[$class] .= ',content';
}
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
    'sortby' => "{$class}.parent ASC, {$class}.menuindex",
    'sortdir' => 'ASC',
    'return' => 'data',
    'scheme' => 'full',
    'limit' => 0,
);
// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);

if (!empty($cache)) {
    $data = $pdoFetch->getCache($scriptProperties);
}
if (empty($data)) {
    $now = time();
    $data = $urls = array();
    $rows = $pdoFetch->run();
    foreach ($rows as $row) {
        if (!empty($useWeblinkUrl) && $row['class_key'] == 'modWebLink') {
            $row['url'] = is_numeric(trim($row['content'], '[]~ '))
                ? $pdoFetch->makeUrl(intval(trim($row['content'], '[]~ ')), $row)
                : $row['content'];
        } else {
            $row['url'] = $pdoFetch->makeUrl($row['id'], $row);
        }

        $time = !empty($row['editedon'])
            ? $row['editedon']
            : $row['createdon'];
        $row['date'] = date('c', $time);

        $datediff = floor(($now - $time) / 86400);
        if ($datediff <= 1) {
            $row['priority'] = '1.0';
            $row['update'] = 'daily';
        } elseif (($datediff > 1) && ($datediff <= 7)) {
            $row['priority'] = '0.75';
            $row['update'] = 'weekly';
        } elseif (($datediff > 7) && ($datediff <= 30)) {
            $row['priority'] = '0.50';
            $row['update'] = 'weekly';
        } else {
            $row['priority'] = '0.25';
            $row['update'] = 'monthly';
        }
        if (!empty($priorityTV) && !empty($row[$priorityTV])) {
            $row['priority'] = $row[$priorityTV];
        }

        // Fix possible duplicates made by modWebLink
        if (!empty($urls[$row['url']])) {
            if ($urls[$row['url']] > $row['date']) {
                continue;
            }
        }
        $urls[$row['url']] = $row['date'];

        // Add item to output
        $data[$row['url']] = $pdoFetch->parseChunk($tpl, $row);
        if (strpos($data[$row['url']], '[[') !== false) {
            $modx->parser->processElementTags('', $data[$row['url']], true, true, '[[', ']]', array(), 10);
        }
    }
    $pdoFetch->addTime('Rows processed');
    if (!empty($cache)) {
        $pdoFetch->setCache($data, $scriptProperties);
    }
}

$output = implode($outputSeparator, $data);
$output = $pdoFetch->getChunk($tplWrapper, array(
    'schema' => $sitemapSchema,
    'output' => $output,
    'items' => $output,
));
$pdoFetch->addTime('Rows wrapped');

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="pdoSitemapLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($forceXML)) {
    header("Content-Type:text/xml");
    @session_write_close();
    exit($output);
} else {
    return $output;
}