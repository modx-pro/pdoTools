<?php

$properties = [];

$tmp = [
    'id' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 1,
        'desc' => 'pdotools_prop_neighbors_limit',
    ],
    'sortby' => [
        'type' => 'textfield',
        'value' => 'menuindex',
    ],
    'sortdir' => [
        'type' => 'textfield',
        'value' => 'asc',
    ],
    'depth' => [
        'type' => 'numberfield',
        'value' => 0,
    ],

    'tplPrev' => [
        'type' => 'textfield',
        'value' => '@INLINE <span class="link-prev"><a href="[[+link]]" class="btn btn-light">&larr; [[+menutitle]]</a></span>',
    ],
    'tplUp' => [
        'type' => 'textfield',
        'value' => '@INLINE <span class="link-up"><a href="[[+link]]" class="btn btn-light">&uarr; [[+menutitle]]</a></span>',
    ],
    'tplNext' => [
        'type' => 'textfield',
        'value' => '@INLINE <span class="link-next"><a href="[[+link]]" class="btn btn-light">[[+menutitle]] &rarr;</a></span>',
    ],
    'tplWrapper' => [
        'type' => 'textfield',
        'value' => '@INLINE <div class="neighbors d-flex justify-content-between">[[+prev]][[+up]][[+next]]</div>',
        'desc' => 'pdotools_prop_neighbors_tplWrapper',
    ],
    'wrapIfEmpty' => [
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
    'showHidden' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'hideContainers' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],

    'toSeparatePlaceholders' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'parents' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'outputSeparator' => [
        'type' => 'textfield',
        'value' => "\n",
    ],
    'showLog' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'fastMode' => [
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
    'loop' => [
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