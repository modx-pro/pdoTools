<?php

$properties = array();

$tmp = array(
	'id' => array(
		'type' => 'numberfield'
		,'value' => ''
	)
	,'field' => array(
		'type' => 'numberfield'
		,'value' => 'pagetitle'
	)
	,'prepareTVs' => array(
		'type' => 'textfield'
		,'value' => '1'
	)
	,'processTVs' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'where' => array(
		'type' => 'textfield'
		,'value' => ''
	)

);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(array(
			'name' => $k
			,'desc' => 'pdotools_prop_'.$k
			,'lexicon' => 'pdotools:properties'
		), $v
	);
}

return $properties;