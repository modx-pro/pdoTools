<?php

$properties = [];

$tmp = [
    'tpl' => [
        'type' => 'textfield',
        'value' => "@INLINE <url>\n\t<loc>[[+url]]</loc>\n\t<lastmod>[[+date]]</lastmod>\n\t<changefreq>[[+update]]</changefreq>\n\t<priority>[[+priority]]</priority>\n</url>",
    ],
    'tplWrapper' => [
        'type' => 'textfield',
        'value' => "@INLINE <?xml version=\"1.0\" encoding=\"[[++modx_charset]]\"?>\n<urlset xmlns=\"[[+schema]]\">\n[[+output]]\n</urlset>",
    ],
    'templates' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'context' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'depth' => [
        'type' => 'numberfield',
        'value' => 10,
    ],
    'showDeleted' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showHidden' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'sitemapSchema' => [
        'type' => 'textfield',
        'value' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
    ],
    'showUnpublished' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'hideUnsearchable' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'resources' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'parents' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'sortby' => [
        'type' => 'textfield',
        'value' => 'menuindex',
    ],
    'sortdir' => [
        'type' => 'textfield',
        'value' => 'asc',
    ],
    'where' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'includeTVs' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'prepareTVs' => [
        'type' => 'textfield',
        'value' => '1',
    ],
    'processTVs' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'outputSeparator' => [
        'type' => 'textfield',
        'value' => "\n",
    ],
    'forceXML' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'useWeblinkUrl' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],

    'cache' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'cacheKey' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'cacheTime' => [
        'type' => 'numberfield',
        'value' => 600,
    ],
];

foreach ($tmp as $k => $v) {
    $properties[] = array_merge([
        'name' => $k,
        'desc' => 'pdotools_prop_' . $k,
        'lexicon' => 'pdotools:properties',
    ], $v);
}

return $properties;