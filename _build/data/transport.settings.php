<?php

$settings = array();

$tmp = array(
    'pdoTools.class' => array(
        'xtype' => 'textfield',
        'value' => 'pdotools.pdotools',
        'key' => 'pdoTools.class',
    ),
    'pdoFetch.class' => array(
        'xtype' => 'textfield',
        'value' => 'pdotools.pdofetch',
        'key' => 'pdoFetch.class',
    ),
    /*
    'pdoParser.class' => array(
        'xtype' => 'textfield',
        'value' => 'pdotools.pdoparser',
        'key' => 'pdoParser.class',
    ),
    */
    'pdotools_class_path' => array(
        'xtype' => 'textfield',
        'value' => '{core_path}components/pdotools/model/',
        'key' => 'pdotools_class_path',
    ),
    'pdofetch_class_path' => array(
        'xtype' => 'textfield',
        'value' => '{core_path}components/pdotools/model/',
        'key' => 'pdofetch_class_path',
    ),
    'fenom_default' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
    ),
    'fenom_parser' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
    ),
    'fenom_php' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
    ),
    'fenom_modx' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
    ),
    'fenom_options' => array(
        'xtype' => 'textarea',
        'value' => '',
    ),
    'fenom_cache' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
    ),
    'fenom_save_on_errors' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
    ),

    'elements_path' => array(
        'xtype' => 'textfield',
        'value' => '{core_path}elements/',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => PKG_NAME_LOWER . '_' . $k,
            'namespace' => PKG_NAME_LOWER,
            'area' => 'pdotools_main',
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}
unset($tmp);

return $settings;