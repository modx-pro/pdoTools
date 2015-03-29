<?php

$properties = array();

$tmp = array(
	'id' => array(
		'type' => 'numberfield',
		'value' => '',
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 1,
		'desc' => 'pdotools_prop_neighbors_limit',
	),
	'sortby' => array(
		'type' => 'textfield',
		'value' => 'menuindex',
	),
	'sortdir' => array(
		'type' => 'textfield',
		'value' => 'asc',
	),

	'tplPrev' => array(
		'type' => 'textfield',
		'value' => '@INLINE <span class="link-prev"><a href="[[+link]]">&larr; [[+menutitle]]</a></span>',
	),
	'tplUp' => array(
		'type' => 'textfield',
		'value' => '@INLINE <span class="link-up">&uarr; <a href="[[+link]]">[[+menutitle]]</a></span>',
	),
	'tplNext' => array(
		'type' => 'textfield',
		'value' => '@INLINE <span class="link-next"><a href="[[+link]]">[[+menutitle]] &rarr;</a></span>',
	),
	'tplWrapper' => array(
		'type' => 'textfield',
		'value' => '@INLINE <div class="neighbors">[[+prev]][[+up]][[+next]]</div>',
		'desc' => 'pdotools_prop_neighbors_tplWrapper',
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

	'toSeparatePlaceholders' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'toPlaceholder' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'outputSeparator' => array(
		'type' => 'textfield',
		'value' => "\n",
	),
	'showLog' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'fastMode' => array(
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
	'useWeblinkUrl' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	'loop' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),

);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(array(
			'name' => $k,
			'desc' => 'pdotools_prop_'.$k,
			'lexicon' => 'pdotools:properties',
		), $v
	);
}

return $properties;