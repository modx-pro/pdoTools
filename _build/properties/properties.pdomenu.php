<?php

$properties = [];

$tmp = [
    // debug
    'showLog' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'fastMode' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'level' => [
        'type' => 'numberfield',
        'value' => 0,
    ],
    'parents' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'displayStart' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'resources' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'templates' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'context' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'cache' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'cacheTime' => [
        'type' => 'numberfield',
        'value' => 3600,
    ],
    'cacheAnonymous' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],

    'plPrefix' => [
        'type' => 'textfield',
        'value' => 'wf.',
    ],
    'showHidden' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showUnpublished' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'showDeleted' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'previewUnpublished' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'hideSubMenus' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],

    'useWeblinkUrl' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],

    'sortdir' => [
        'type' => 'list',
        'options' => [
            ['text' => 'ASC', 'value' => 'ASC'],
            ['text' => 'DESC', 'value' => 'DESC'],
        ],
        'value' => 'ASC',
    ],
    'sortby' => [
        'type' => 'textfield',
        'value' => 'menuindex',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 0,
    ],
    'offset' => [
        'type' => 'numberfield',
        'value' => 0,
    ],

    // cssTpl
    // jsTpl

    // textOfLinks
    // titleOfLinks

    'rowIdPrefix' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'firstClass' => [
        'type' => 'textfield',
        'value' => 'first',
    ],
    'lastClass' => [
        'type' => 'textfield',
        'value' => 'last',
    ],
    'hereClass' => [
        'type' => 'textfield',
        'value' => 'active',
    ],
    'parentClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'rowClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'outerClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'innerClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'levelClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'selfClass' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'webLinkClass' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'tplOuter' => [
        'type' => 'textfield',
        'value' => '@INLINE <ul[[+classes]]>[[+wrapper]]</ul>',
    ],
    'tpl' => [
        'type' => 'textfield',
        'value' => '@INLINE <li[[+classes]]><a href="[[+link]]" [[+attributes]]>[[+menutitle]]</a>[[+wrapper]]</li>',
    ],
    'tplParentRow' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplParentRowHere' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplHere' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplInner' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplInnerRow' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplInnerHere' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplParentRowActive' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplCategoryFolder' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'tplStart' => [
        'type' => 'textfield',
        'value' => '@INLINE <h2[[+classes]]>[[+menutitle]]</h2>[[+wrapper]]',
    ],

    'checkPermissions' => [
        'type' => 'textfield',
        //'value' => 'load',
        'value' => '',
    ],
    'hereId' => [
        'type' => 'numberfield',
        'value' => '',
    ],

    'where' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'select' => [
        'type' => 'textfield',
        'value' => '',
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

    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'countChildren' => [
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