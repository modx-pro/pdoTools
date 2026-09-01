<?php

$properties = [];

$tmp = [

    'id' => [
        'type' => 'numberfield',
        'value' => 0,
    ],
    'exclude' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 3,
        'desc' => 'pdotools_prop_title_limit',
    ],
    'titleField' => [
        'type' => 'textfield',
        'value' => 'longtitle',
    ],

    'cache' => [
        'type' => 'numberfield',
        'value' => false,
        'desc' => 'pdotools_prop_title_cache',
    ],
    'cacheTime' => [
        'type' => 'numberfield',
        'value' => 0,
    ],

    'tplPages' => [
        'type' => 'textfield',
        'value' => '@INLINE [[%pdopage_page]] [[+page]] [[%pdopage_from]] [[+pageCount]]',
    ],
    'pageVarKey' => [
        'type' => 'textfield',
        'value' => 'page',
    ],

    'tplSearch' => [
        'type' => 'textfield',
        'value' => '@INLINE «[[+mse2_query]]»',
    ],
    'queryVarKey' => [
        'type' => 'textfield',
        'value' => 'query',
    ],
    'minQuery' => [
        'type' => 'numberfield',
        'value' => 3,
    ],

    'outputSeparator' => [
        'type' => 'textfield',
        'value' => ' / ',
        'desc' => 'pdotools_prop_title_outputSeparator',
    ],

    'registerJs' => [
        'type' => 'combo-boolean',
        'value' => false,
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
