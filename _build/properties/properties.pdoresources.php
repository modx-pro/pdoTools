<?php

$properties = [];

$tmp = [
    'tpl' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'returnIds' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showLog' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'fastMode' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'sortby' => [
        'type' => 'textfield',
        'value' => 'publishedon',
    ],
    'sortbyTV' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'sortbyTVType' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'sortdir' => [
        'type' => 'list',
        'options' => [
            ['text' => 'ASC', 'value' => 'ASC'],
            ['text' => 'DESC', 'value' => 'DESC'],
        ],
        'value' => 'DESC',
    ],
    'sortdirTV' => [
        'type' => 'list',
        'options' => [
            ['text' => 'ASC', 'value' => 'ASC'],
            ['text' => 'DESC', 'value' => 'DESC'],
        ],
        'value' => 'ASC',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 10,
    ],
    'offset' => [
        'type' => 'numberfield',
        'value' => 0,
    ],
    'depth' => [
        'type' => 'numberfield',
        'value' => 10,
    ],
    'outputSeparator' => [
        'type' => 'textfield',
        'value' => "\n",
    ],
    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'parents' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'includeContent' => [
        'type' => 'combo-boolean',
        'value' => false,
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
    'tvPrefix' => [
        'type' => 'textfield',
        'value' => 'tv.',
    ],
    'tvFilters' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tvFiltersAndDelimiter' => [
        'type' => 'textfield',
        'value' => ',',
    ],
    'tvFiltersOrDelimiter' => [
        'type' => 'textfield',
        'value' => '||',
    ],

    'where' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'showUnpublished' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showDeleted' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showHidden' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'hideContainers' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'context' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'idx' => [
        'type' => 'numberfield',
        'value' => '',
    ],

    'first' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'last' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'tplFirst' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplLast' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplOdd' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplWrapper' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'wrapIfEmpty' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'totalVar' => [
        'type' => 'textfield',
        'value' => 'total',
    ],
    'resources' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplCondition' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplOperator' => [
        'type' => 'list',
        'options' => [
            ['text' => 'is equal to', 'value' => '=='],
            ['text' => 'is not equal to', 'value' => '!='],
            ['text' => 'less than', 'value' => '<'],
            ['text' => 'less than or equal to', 'value' => '<='],
            ['text' => 'greater than or equal to', 'value' => '>='],
            ['text' => 'is empty', 'value' => 'empty'],
            ['text' => 'is not empty', 'value' => '!empty'],
            ['text' => 'is null', 'value' => 'null'],
            ['text' => 'is in array', 'value' => 'inarray'],
            ['text' => 'is between', 'value' => 'between'],
        ],
        'value' => '==',
    ],
    'conditionalTpls' => [
        'type' => 'textarea',
        'value' => '',
    ],
    'select' => [
        'type' => 'textarea',
        'value' => '',
    ],
    'toSeparatePlaceholders' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'loadModels' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'scheme' => [
        'type' => 'list',
        'options' => [
            ['name' => 'System default', 'value' => ''],
            ['name' => '-1 (relative to site_url)', 'value' => -1],
            ['name' => 'full (absolute, prepended with site_url)', 'value' => 'full'],
            ['name' => 'abs (absolute, prepended with base_url)', 'value' => 'abs'],
            ['name' => 'http (absolute, forced to http scheme)', 'value' => 'http'],
            ['name' => 'https (absolute, forced to https scheme)', 'value' => 'https'],
        ],
        'value' => '',
    ],
    'useWeblinkUrl' => [
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