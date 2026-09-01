<?php

$properties = [];

$tmp = [
    'tpl' => [
        'type' => 'textfield',
        'value' => '@INLINE <li>[[+date]] <a href="[[+link]]">[[+menutitle]]</a></li>',
    ],
    'tplYear' => [
        'type' => 'textfield',
        'value' => '@INLINE <h3>[[+year]] <sup>([[+count]])</sup></h3><ul>[[+wrapper]]</ul>',
    ],
    'tplMonth' => [
        'type' => 'textfield',
        'value' => '@INLINE <li><h4>[[+month_name]] <sup>([[+count]])</sup></h4><ul>[[+wrapper]]</ul></li>',
    ],
    'tplDay' => [
        'type' => 'textfield',
        'value' => '@INLINE <li><h5>[[+day]] <sup>([[+count]])</sup></h5><ul>[[+wrapper]]</ul></li>',
    ],
    'tplWrapper' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'wrapIfEmpty' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],

    'dateField' => [
        'type' => 'textfield',
        'value' => 'createdon',
    ],
    'dateFormat' => [
        'type' => 'textfield',
        'value' => '%H:%M',
    ],

    'showLog' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'sortby' => [
        'type' => 'textfield',
        'value' => 'createdon',
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
        'value' => 0,
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

    'totalVar' => [
        'type' => 'textfield',
        'value' => 'total',
    ],
    'resources' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'select' => [
        'type' => 'textarea',
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