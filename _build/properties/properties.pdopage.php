<?php

$properties = array();

$tmp = array(
    'plPrefix' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'limit' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'maxLimit' => array(
        'type' => 'numberfield',
        'value' => 100,
    ),
    'offset' => array(
        'type' => 'numberfield',
        'value' => '',
    ),

    'page' => array(
        'type' => 'numberfield',
        'value' => '',
    ),
    'pageVarKey' => array(
        'type' => 'textfield',
        'value' => 'page',
    ),
    'totalVar' => array(
        'type' => 'textfield',
        'value' => 'page.total',
    ),
    'pageLimit' => array(
        'type' => 'numberfield',
        'value' => 5,
    ),

    'element' => array(
        'type' => 'textfield',
        'value' => 'pdoResources',
    ),

    'pageNavVar' => array(
        'type' => 'textfield',
        'value' => 'page.nav',
    ),
    'pageCountVar' => array(
        'type' => 'textfield',
        'value' => 'pageCount',
    ),
    'pageLinkScheme' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'tplPage' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[+pageNo]]</a></li>',
    ),
    'tplPageWrapper' => array(
        'type' => 'textfield',
        'value' => '@INLINE <ul class="pagination">[[+first]][[+prev]][[+pages]][[+next]][[+last]]</ul>',
    ),
    'tplPageActive' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item active"><a class="page-link" href="[[+href]]">[[+pageNo]]</a></li>',
    ),
    'tplPageFirst' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[%pdopage_first]]</a></li>',
    ),
    'tplPageLast' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">[[%pdopage_last]]</a></li>',
    ),
    'tplPagePrev' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">&laquo;</a></li>',
    ),
    'tplPageNext' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item"><a class="page-link" href="[[+href]]">&raquo;</a></li>',
    ),
    'tplPageSkip' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><span class="page-link">...</span></li>',
    ),

    'tplPageFirstEmpty' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><span class="page-link">[[%pdopage_first]]</span></li>',
    ),
    'tplPageLastEmpty' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><span class="page-link">[[%pdopage_last]]</span></li>',
    ),
    'tplPagePrevEmpty' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><span class="page-link">&laquo;</span></li>',
    ),
    'tplPageNextEmpty' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li class="page-item disabled"><span class="page-link" >&raquo;</span></li>',
    ),

    'cache' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'cacheTime' => array(
        'type' => 'numberfield',
        'value' => 3600,
    ),
    'cacheAnonymous' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),

    'toPlaceholder' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'ajax' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'ajaxMode' => array(
        'type' => 'list',
        'value' => '',
        'options' => array(
            array('text' => 'None', 'value' => ''),
            array('text' => 'Default', 'value' => 'default'),
            array('text' => 'Scroll', 'value' => 'scroll'),
            array('text' => 'Button', 'value' => 'button'),
        ),
    ),
    'ajaxElemWrapper' => array(
        'type' => 'textfield',
        'value' => '#pdopage',
    ),
    'ajaxElemRows' => array(
        'type' => 'textfield',
        'value' => '#pdopage .rows',
    ),
    'ajaxElemPagination' => array(
        'type' => 'textfield',
        'value' => '#pdopage .pagination',
    ),
    'ajaxElemLink' => array(
        'type' => 'textfield',
        'value' => '#pdopage .pagination a',
    ),
    'ajaxElemMore' => array(
        'type' => 'textfield',
        'value' => '#pdopage .btn-more',
    ),
    'ajaxTplMore' => array(
        'type' => 'textfield',
        'value' => '@INLINE <button class="btn btn-primary btn-more">[[%pdopage_more]]</button>',
    ),
    'ajaxHistory' => array(
        'type' => 'list',
        'value' => '',
        'options' => array(
            array('text' => 'Auto', 'value' => ''),
            array('text' => 'Enabled', 'value' => 1),
            array('text' => 'Disabled', 'value' => 0),
        ),
    ),
    'frontend_js' => array(
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]js/pdopage.min.js',
    ),
    'frontend_css' => array(
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]css/pdopage.min.css',
    ),
    'setMeta' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'strictMode' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),

);

foreach ($tmp as $k => $v) {
    $properties[] = array_merge(array(
        'name' => $k,
        'desc' => 'pdotools_prop_' . $k,
        'lexicon' => 'pdotools:properties',
    ), $v);
}

return $properties;
