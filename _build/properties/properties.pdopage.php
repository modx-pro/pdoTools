<?php

$properties = [];

$tmp = [
    'plPrefix' => [
        'type' => 'textfield',
        'value' => '',
    ],
    'limit' => [
        'type' => 'numberfield',
        'value' => 10,
    ],
    'maxLimit' => [
        'type' => 'numberfield',
        'value' => 100,
    ],
    'offset' => [
        'type' => 'numberfield',
        'value' => '',
    ],

    'page' => [
        'type' => 'numberfield',
        'value' => '',
    ],
    'pageVarKey' => [
        'type' => 'textfield',
        'value' => 'page',
    ],
    'totalVar' => [
        'type' => 'textfield',
        'value' => 'page.total',
    ],
    'pageLimit' => [
        'type' => 'numberfield',
        'value' => 5,
    ],

    'element' => [
        'type' => 'textfield',
        'value' => 'pdoResources',
    ],

    'pageNavVar' => [
        'type' => 'textfield',
        'value' => 'page.nav',
    ],
    'pageCountVar' => [
        'type' => 'textfield',
        'value' => 'pageCount',
    ],
    'pageLinkScheme' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'tplPage' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[+pageNo]]</a></li>',
    ],
    'tplPageWrapper' => [
        'type' => 'textfield',
        'value' => '@INLINE <ul class="pagination">[[+first]][[+prev]][[+pages]][[+next]][[+last]]</ul>',
    ],
    'tplPageActive' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item active"><a class="page-link" href="[[+href]]">[[+pageNo]]</a></li>',
    ],
    'tplPageFirst' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[%pdopage_first]]</a></li>',
    ],
    'tplPageLast' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[%pdopage_last]]</a></li>',
    ],
    'tplPagePrev' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">&laquo;</a></li>',
    ],
    'tplPageNext' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">&raquo;</a></li>',
    ],
    'tplPageSkip' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><a class="page-link" href="#">...</a></li>',
    ],

    'tplPageFirstEmpty' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><a class="page-link" href="#">[[%pdopage_first]]</a></li>',
    ],
    'tplPageLastEmpty' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><a class="page-link" href="#">[[%pdopage_last]]</a></li>',
    ],
    'tplPagePrevEmpty' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>',
    ],
    'tplPageNextEmpty' => [
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>',
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

    'toPlaceholder' => [
        'type' => 'textfield',
        'value' => '',
    ],

    'ajax' => [
        'type' => 'combo-boolean',
        'value' => false,
    ],
    'ajaxMode' => [
        'type' => 'list',
        'value' => '',
        'options' => [
            ['text' => 'None', 'value' => ''],
            ['text' => 'Default', 'value' => 'default'],
            ['text' => 'Scroll', 'value' => 'scroll'],
            ['text' => 'Button', 'value' => 'button'],
        ],
    ],
    'ajaxElemWrapper' => [
        'type' => 'textfield',
        'value' => '#pdopage',
    ],
    'ajaxElemRows' => [
        'type' => 'textfield',
        'value' => '#pdopage .rows',
    ],
    'ajaxElemPagination' => [
        'type' => 'textfield',
        'value' => '#pdopage .pagination',
    ],
    'ajaxElemLink' => [
        'type' => 'textfield',
        'value' => '#pdopage .pagination a',
    ],
    'ajaxElemMore' => [
        'type' => 'textfield',
        'value' => '#pdopage .btn-more',
    ],
    'ajaxTplMore' => [
        'type' => 'textfield',
        'value' => '@INLINE <button class="btn btn-primary btn-more">[[%pdopage_more]]</button>',
    ],
    'ajaxHistory' => [
        'type' => 'list',
        'value' => '',
        'options' => [
            ['text' => 'Auto', 'value' => ''],
            ['text' => 'Enabled', 'value' => 1],
            ['text' => 'Disabled', 'value' => 0],
        ],
    ],
    'frontend_js' => [
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]js/pdopage.min.js',
    ],
    'frontend_css' => [
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]css/pdopage.min.css',
    ],
    'setMeta' => [
        'type' => 'combo-boolean',
        'value' => true,
    ],
    'strictMode' => [
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