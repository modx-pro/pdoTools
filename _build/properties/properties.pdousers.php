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
        'value' => 'modUser.id',
    ],
    'sortdir' => [
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
    'outputSeparator' => [
        'type' => 'textfield',
        'value' => "\n",
    ],
    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'groups' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'roles' => [
        'type' => 'textfield',
        'value' => false,
    ],
    'users' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'where' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'showInactive' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showBlocked' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'idx' => [
        'type' => 'numberfield',
        'value' => '',
    ]
    ,
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

];

foreach ($tmp as $k => $v) {
    $properties[] = array_merge([
        'name' => $k,
        'desc' => 'pdotools_prop_' . $k,
        'lexicon' => 'pdotools:properties',
    ], $v);
}

return $properties;