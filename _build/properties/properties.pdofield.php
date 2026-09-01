<?php

$properties = [];

$tmp = [
    'id' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'field' => [
        'type' => 'textfield',
        'value' => 'pagetitle',
    ],
    'prepareTVs' => [
        'type' => 'textfield',
        'value' => '1',
    ],
    'processTVs' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'where' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'context' => [
        'type' => 'textfield',
        'desc' => 'pdotools_prop_field_context',
        'value' => '',
    ],
    'top' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'topLevel' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'default' => [
        'type' => 'textfield',
        'desc' => 'pdotools_prop_field_default',
        'value' => '',
    ],
    'output' => [
        'type' => 'textfield',
        'desc' => 'pdotools_prop_field_output',
        'value' => '',
    ],
    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'ultimate' => [
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
