<?php

$properties = array();

$tmp = array(
    'tpl' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li>[[+date]] <a href="[[+link]]">[[+menutitle]]</a></li>',
    ),
    'tplYear' => array(
        'type' => 'textfield',
        'value' => '@INLINE <h3>[[+year]] <sup>([[+count]])</sup></h3><ul>[[+wrapper]]</ul>',
    ),
    'tplMonth' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li><h4>[[+month_name]] <sup>([[+count]])</sup></h4><ul>[[+wrapper]]</ul></li>',
    ),
    'tplDay' => array(
        'type' => 'textfield',
        'value' => '@INLINE <li><h5>[[+day]] <sup>([[+count]])</sup></h5><ul>[[+wrapper]]</ul></li>',
    ),
    'tplWrapper' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'wrapIfEmpty' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),

    'dateField' => array(
        'type' => 'textfield',
        'value' => 'createdon',
    ),
    'dateFormat' => array(
        'type' => 'textfield',
        'value' => '%H:%M',
    ),

    'showLog' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'sortby' => array(
        'type' => 'textfield',
        'value' => 'createdon',
    ),
    'sortbyTV' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'sortbyTVType' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'sortdir' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'ASC', 'value' => 'ASC'),
            array('text' => 'DESC', 'value' => 'DESC'),
        ),
        'value' => 'DESC',
    ),
    'sortdirTV' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'ASC', 'value' => 'ASC'),
            array('text' => 'DESC', 'value' => 'DESC'),
        ),
        'value' => 'ASC',
    ),
    'limit' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'offset' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'depth' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'outputSeparator' => array(
        'type' => 'textfield',
        'value' => "\n",
    ),
    'toPlaceholder' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'parents' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'includeContent' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),

    'includeTVs' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'prepareTVs' => array(
        'type' => 'textfield',
        'value' => '1',
    ),
    'processTVs' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tvPrefix' => array(
        'type' => 'textfield',
        'value' => 'tv.',
    ),

    'where' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'showUnpublished' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showDeleted' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showHidden' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'hideContainers' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'context' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'totalVar' => array(
        'type' => 'textfield',
        'value' => 'total',
    ),
    'resources' => array(
        'type' => 'textfield',
        'value' => '',
    ),

    'select' => array(
        'type' => 'textarea',
        'value' => '',
    ),

    'scheme' => array(
        'type' => 'list',
        'options' => array(
            array('name' => 'System default', 'value' => ''),
            array('name' => '-1 (relative to site_url)', 'value' => -1),
            array('name' => 'full (absolute, prepended with site_url)', 'value' => 'full'),
            array('name' => 'abs (absolute, prepended with base_url)', 'value' => 'abs'),
            array('name' => 'http (absolute, forced to http scheme)', 'value' => 'http'),
            array('name' => 'https (absolute, forced to https scheme)', 'value' => 'https'),
        ),
        'value' => '',
    ),
    'useWeblinkUrl' => array(
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