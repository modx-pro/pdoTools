<?php

$properties = array();

$tmp = array(
    // debug
    'showLog' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'fastMode' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'level' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'parents' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'displayStart' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'resources' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'templates' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'context' => array(
        'type' => 'textfield',
        'value' => '',
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

    'plPrefix' => array(
        'type' => 'textfield',
        'value' => 'wf.',
    ),
    'showHidden' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showUnpublished' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showDeleted' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'previewUnpublished' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'hideSubMenus' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),

    'useWeblinkUrl' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),

    'sortdir' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'ASC', 'value' => 'ASC'),
            array('text' => 'DESC', 'value' => 'DESC'),
        ),
        'value' => 'ASC',
    ),
    'sortby' => array(
        'type' => 'textfield',
        'value' => 'menuindex',
    ),
    'limit' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'offset' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),

    // cssTpl
    // jsTpl

    // textOfLinks
    // titleOfLinks

    'rowIdPrefix' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'firstClass' => array(
        'type' => 'textfield',
        'value' => 'first',
    ),
    'lastClass' => array(
        'type' => 'textfield',
        'value' => 'last',
    ),
    'hereClass' => array(
        'type' => 'textfield',
        'value' => 'active',
    ),
    'parentClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'rowClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'outerClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'innerClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'levelClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'selfClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'webLinkClass' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'tplOuter' => array(
        'type' => 'textfield',
        'value' => '@INLINE <ul[[+classes]]>[[+wrapper]]</ul>',
    ),
    'tpl' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li[[+classes]]><a href="[[+link]]" [[+attributes]]>[[+menutitle]]</a>[[+wrapper]]</li>',
    ),
    'tplParentRow' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplParentRowHere' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplHere' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplInner' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplInnerRow' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplInnerHere' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplParentRowActive' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplCategoryFolder' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplStart' => array(
        'type' => 'textfield',
        'value' => '@INLINE <h2[[+classes]]>[[+menutitle]]</h2>[[+wrapper]]',
    ),

    'checkPermissions' => array(
        'type' => 'textfield',
        //'value' => 'load',
        'value' => '',
    ),
    'hereId' => array(
        'type' => 'numberfield',
        'value' => '',
    ),

    'where' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'select' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'scheme' => array(
        'type' => 'list',
        'options' => array(
            array(
                'name' => 'System default',
                'value' => '',
            ),
            array(
                'name' => '-1 (relative to site_url)',
                'value' => -1,
            ),
            array(
                'name' => 'full (absolute, prepended with site_url)',
                'value' => 'full',
            ),
            array(
                'name' => 'abs (absolute, prepended with base_url)',
                'value' => 'abs',
            ),
            array(
                'name' => 'http (absolute, forced to http scheme)',
                'value' => 'http',
            ),
            array(
                'name' => 'https (absolute, forced to https scheme)',
                'value' => 'https',
            ),
        ),
        'value' => '',
    ),

    'toPlaceholder' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'countChildren' => array(
        'type' => 'combo-boolean',
        'value' => false,
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