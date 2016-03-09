<?php

$properties = array();

$tmp = array(
    'tpl' => array(
        'type' => 'textfield',
        'value' => "@INLINE <url>\n\t<loc>[[+url]]</loc>\n\t<lastmod>[[+date]]</lastmod>\n\t<changefreq>[[+update]]</changefreq>\n\t<priority>[[+priority]]</priority>\n</url>",
    ),
    'tplWrapper' => array(
        'type' => 'textfield',
        'value' => "@INLINE <?xml version=\"1.0\" encoding=\"[[++modx_charset]]\"?>\n<urlset xmlns=\"[[+schema]]\">\n[[+output]]\n</urlset>",
    ),
    'templates' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'context' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'depth' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'showDeleted' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showHidden' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'sitemapSchema' => array(
        'type' => 'textfield',
        'value' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
    ),
    'showUnpublished' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'hideUnsearchable' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'resources' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'parents' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'sortby' => array(
        'type' => 'textfield',
        'value' => 'menuindex',
    ),
    'sortdir' => array(
        'type' => 'textfield',
        'value' => 'asc',
    ),
    'where' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'includeTVs' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'prepareTVs' => array(
        'type' => 'textfield',
        'value' => '1',
    ),
    'processTVs' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'outputSeparator' => array(
        'type' => 'textfield',
        'value' => "\n",
    ),
    'forceXML' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'useWeblinkUrl' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),

    'cache' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'cacheKey' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'cacheTime' => array(
        'type' => 'numberfield',
        'value' => 600,
    ),
);

foreach ($tmp as $k => $v) {
    $properties[] = array_merge(array(
        'name' => $k,
        'desc' => 'pdotools_prop_' . $k,
        'lexicon' => 'pdotools:properties',
    ), $v);
}

return $properties;