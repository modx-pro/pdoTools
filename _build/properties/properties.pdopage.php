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

	'element' => array(
		'type' => 'textfield',
		'value' => 'pdoResources',
	),
	// elementClass
	/*
	'class' => array(
		'type' => 'textfield',
		'value' => '',
	),
	*/

	'pageNavVar' => array(
		'type' => 'textfield',
		'value' => 'page.nav',
	),
	// pageNavTpl
	'tplPage' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li[[+classes]]><a[[+classes]][[+title]] href="[[+href]]">[[+pageNo]]</a></li>',
	),
	// pageNavOuterTpl
	'tplPageWrapper' => array(
		'type' => 'textfield',
		'value' => '@INLINE [[+first]][[+prev]][[+pages]][[+next]][[+last]]',
	),
	// pageActiveTpl
	'tplPageActive' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li[[+activeClasses]]><a[[+activeClasses:default=` class="active"`]][[+title]] href="[[+href]]">[[+pageNo]]</a></li>',
	),
	// pageFirstTpl
	'tplPageFirst' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">First</a></li>',
	),
	// pageLastTpl
	'tplPageLast' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">Last</a></li>',
	),
	// pagePrevTpl
	'tplPagePrev' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">&lt;&lt;</a></li>',
	),
	// pageNextTpl
	'tplPageNext' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a[[+classes]][[+title]] href="[[+href]]">&gt;&gt;</a></li>',
	),
	// pageSkipTpl
	'tplPageSkip' => array(
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
	'toPlaceholder' => array(
		'type' => 'textfield',
		'value' => '',
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