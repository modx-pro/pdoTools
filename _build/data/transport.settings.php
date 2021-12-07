<?php

/** @var \MODX\Revolution\modX $modx */

$settings = [];

$tmp = [
    'fenom_default' => [
        'xtype' => 'combo-boolean',
        'value' => true,
    ],
    'fenom_parser' => [
        'xtype' => 'combo-boolean',
        'value' => false,
    ],
    'fenom_php' => [
        'xtype' => 'combo-boolean',
        'value' => false,
    ],
    'fenom_modx' => [
        'xtype' => 'combo-boolean',
        'value' => false,
    ],
    'fenom_options' => [
        'xtype' => 'textarea',
        'value' => '',
    ],
    'fenom_cache' => [
        'xtype' => 'combo-boolean',
        'value' => false,
    ],
    'fenom_save_on_errors' => [
        'xtype' => 'combo-boolean',
        'value' => false,
    ],
    'elements_path' => [
        'xtype' => 'textfield',
        'value' => '{core_path}elements/',
    ],
    'filter_path' => [
        'xtype' => 'combo-boolean',
        'value' => true,
    ],
];

foreach ($tmp as $k => $v) {
    /** @var MODX\Revolution\modSystemSetting $setting */
    $setting = $modx->newObject(MODX\Revolution\modSystemSetting::class);
    $setting->fromArray(
        array_merge(
            [
                'key' => PKG_NAME_LOWER . '_' . $k,
                'namespace' => PKG_NAME_LOWER,
                'area' => 'pdotools_main',
            ],
            $v
        ),
        '',
        true,
        true
    );

    $settings[] = $setting;
}
unset($tmp);

return $settings;