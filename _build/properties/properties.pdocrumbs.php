<?php

$properties = array();

$tmp = array(
	'showLog' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'fastMode' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'from' => array(
		'type' => 'numberfield'
		,'value' => 0
	)
	,'to' => array(
		'type' => 'numberfield'
		,'value' => ''
	)
	,'limit' => array(
		'type' => 'numberfield'
		,'value' => 10
	)
	,'exclude' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'outputSeparator' => array(
		'type' => 'textfield'
		,'value' => '&nbsp;&rarr;&nbsp;'
	)
	,'toPlaceholder' => array(
		'type' => 'textfield'
		,'value' => ''
	)

	,'includeTVs' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'prepareTVs' => array(
		'type' => 'textfield'
		,'value' => '1'
	)
	,'processTVs' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'tvPrefix' => array(
		'type' => 'textfield'
		,'value' => 'tv.'
	)

	,'where' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'showUnpublished' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'showDeleted' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'showHidden' => array(
		'type' => 'combo-boolean'
		,'value' => true
	)
	,'hideContainers' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)

	,'tpl' => array(
		'type' => 'textfield'
		,'value' => '@INLINE <a href="[[+link]]">[[+menutitle]]</a>'
	)
	,'tplCurrent' => array(
		'type' => 'textfield'
		,'value' => '@INLINE <span>[[+menutitle]]</span>'
	)
	,'tplMax' => array(
		'type' => 'textfield'
		,'value' => '@INLINE <span>&nbsp;...&nbsp;</span>'
	)
	,'tplHome' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'tplWrapper' => array(
		'type' => 'textfield'
		,'value' => '@INLINE <div class="breadcrumbs">[[+output]]</div>'
	)
	,'wrapIfEmpty' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)

	,'showCurrent' => array(
		'type' => 'combo-boolean'
		,'value' => true
	)
	,'showHome' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'showAtHome' => array(
		'type' => 'combo-boolean'
		,'value' => true
	)
	,'hideSingle' => array(
		'type' => 'combo-boolean'
		,'value' => false
	)
	,'direction' => array(
		'type' => 'list'
		,'options' => array(
			array(
				'name' => 'Left To Right (ltr)'
				,'value' => 'ltr'
			)
			,array(
				'name' => 'Right To Left (rtl)'
				,'value' => 'rtl'
			)
		)
		,'value' => 'ltr'
	)
	,'scheme' => array(
		'type' => 'list'
		,'options' => array(
			array(
				'name' => 'System default'
				,'value' => ''
			),
			array(
				'name' => '-1 (relative to site_url)'
				,'value' => -1
			)
			,array(
				'name' => 'full (absolute, prepended with site_url)'
				,'value' => 'full'
			)
			,array(
				'name' => 'abs (absolute, prepended with base_url)'
				,'value' => 'abs'
			)
			,array(
				'name' => 'http (absolute, forced to http scheme)'
				,'value' => 'http'
			)
			,array(
				'name' => 'https (absolute, forced to https scheme)'
				,'value' => 'https'
			)
		)
		,'value' => ''
	),
	'useWeblinkUrl' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),

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