<?php

$settings = array();

$tmp = array(
	'pdoTools.class' => array(
		'xtype' => 'textfield',
		'value' => 'pdotools.pdotools',
		'key' => 'pdoTools.class'
	),
	'pdoFetch.class' => array(
		'xtype' => 'textfield',
		'value' => 'pdotools.pdofetch',
		'key' => 'pdoFetch.class'
	),
	/*
	'parser_class' => array(
		'xtype' => 'textfield',
		'value' => 'pdoParser',
		'key' => 'parser_class'
	),
	'parser_class_path' => array(
		'xtype' => 'textfield',
		'value' => '{core_path}components/pdotools/model/pdotools/',
		'key' => 'parser_class_path'
	),
	*/
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => PKG_NAME_LOWER.'_'.$k,
			'namespace' => PKG_NAME_LOWER,
			'area' => 'pdotools_main',
		), $v
	),'',true,true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;