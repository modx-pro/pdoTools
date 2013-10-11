<?php

$properties = array();

$tmp = array(
	// namespace
	'plPrefix' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => '',
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
		'value' => '',
	),
	'totalVar' => array(
		'type' => 'numberfield',
		'value' => '',
	),
	'pageLimit' => array(
		'type' => 'numberfield',
		'value' => '',
	),

	// elementClass
	'class' => array(
		'type' => 'textfield',
		'value' => '',
	),

	'pageNavVar' => array(
		'type' => 'textfield',
		'value' => 'page.nav',
	),
	// pageNavTpl
	'tpl' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li[[+classes]]><a[[+classes]][[+title]] href="[[+href]]">[[+pageNo]]</a></li>',
	),
	// pageNavOuterTpl
	'tplWrapper' => array(
		'type' => 'textfield',
		'value' => '@INLINE [[+first]][[+prev]][[+pages]][[+next]][[+last]]',
	),
	// pageActiveTpl
	'tplActive' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li[[+activeClasses]]><a[[+activeClasses:default=` class="active"`]][[+title]] href="[[+href]]">[[+pageNo]]</a></li>',
	),
	// pageFirstTpl
	'tplFirst' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">First</a></li>',
	),
	// pageLastTpl
	'tplLast' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">Last</a></li>',
	),
	// pagePrevTpl
	'tplPrev' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">&lt;&lt;</a></li>',
	),
	// pageNextTpl
	'tplNext' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">&gt;&gt;</a></li>',
	),
	// pageSkipTpl
	'tplSkip' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><span>...</span></li>',
	),


	'cache' => array(
		'type' => 'textfield',
		'options' => array(
			array('text' => 'Disabled','value' => ''),
			array('text' => 'URI','value' => 'uri'),
			array('text' => 'Custom','value' => 'custom'),
			array('text' => 'MODX','value' => 'modx'),
		),
		'value' => '',
	),
	'cachePageKey' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'cache_key' => array(
		'type' => 'textfield',
		'value' => 'resource',
	),
	'cache_handler' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'cache_expires' => array(
		'type' => 'numberfield',
		'value' => '',
	),

	'pageNavScheme' => array(
		'type' => 'textfield',
		'value' => '',
	),

	'showEdgePages' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	/*
	'strictMode' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),
	*/



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