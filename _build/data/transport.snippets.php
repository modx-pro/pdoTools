<?php

/** @var MODX\Revolution\modX $modx */

$snippets = [];

$tmp = [
    'pdoResources' => 'pdoresources',
    'pdoUsers' => 'pdousers',
    'pdoCrumbs' => 'pdocrumbs',
    'pdoField' => 'pdofield',
    'pdoSitemap' => 'pdositemap',
    'pdoNeighbors' => 'pdoneighbors',
    'pdoPage' => 'pdopage',
    'pdoMenu' => 'pdomenu',
    'pdoTitle' => 'pdotitle',
    'pdoArchive' => 'pdoarchive',
];

foreach ($tmp as $k => $v) {
    /** @var MODX\Revolution\modSnippet $snippet */
    $snippet = $modx->newObject(MODX\Revolution\modSnippet::class);
    /** @noinspection PhpUndefinedVariableInspection */
    $snippet->fromArray([
        'id' => 0,
        'name' => $k,
        'description' => '',
        'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/snippet.' . $v . '.php'),
        'static' => BUILD_SNIPPET_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/snippet.' . $v . '.php',
    ], '', true, true);

    /** @noinspection PhpIncludeInspection */
    $properties = include $sources['build'] . 'properties/properties.' . $v . '.php';
    $snippet->setProperties($properties);

    $snippets[] = $snippet;
}
unset($properties);

return $snippets;