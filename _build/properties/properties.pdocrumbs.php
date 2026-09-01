<?php

$properties = [];

$tmp = [
    'showLog' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'fastMode' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'from' => [
        'type' => 'numberfield',
        'value' => 0,
    ],
    'to' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'customParents' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 10,
    ],
    'exclude' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'outputSeparator' => [
        'type' => 'textfield',
        'value' => "\n",
    ],
    'toPlaceholder' => [
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
    'tvPrefix' => [
        'type' => 'textfield',
        'value' => 'tv.',
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

    'tpl' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="breadcrumb-item"><a href="[[+link]]">[[+menutitle]]</a></li>',
    ],
    'tplCurrent' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="breadcrumb-item active">[[+menutitle]]</li>',
    ],
    'tplMax' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="breadcrumb-item disabled">&nbsp;...&nbsp;</li>',
    ],
    'tplHome' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplWrapper' => [
        'type' => 'textfield',
        'value' => '@INLINE <ol class="breadcrumb">[[+output]]</ol>',
    ],
    'wrapIfEmpty' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],

    'showCurrent' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'showHome' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showAtHome' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'hideSingle' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'direction' => [
        'type' => 'list',
        'options' => [
            [
                'name' => 'Left To Right (ltr)',
                'value' => 'ltr',
            ],
            [
                'name' => 'Right To Left (rtl)',
                'value' => 'rtl',
            ],
        ],
        'value' => 'ltr',
    ],
    'scheme' => [
        'type' => 'list',
        'options' => [
            [
                'name' => 'System default',
                'value' => '',
            ],
            [
                'name' => '-1 (relative to site_url)',
                'value' => -1,
            ],
            [
                'name' => 'full (absolute, prepended with site_url)',
                'value' => 'full',
            ],
            [
                'name' => 'abs (absolute, prepended with base_url)',
                'value' => 'abs',
            ],
            [
                'name' => 'http (absolute, forced to http scheme)',
                'value' => 'http',
            ],
            [
                'name' => 'https (absolute, forced to https scheme)',
                'value' => 'https',
            ],
        ],
        'value' => '',
    ],
    'useWeblinkUrl' => [
        'type' => 'combo-boolean',
        'value' => true,
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
