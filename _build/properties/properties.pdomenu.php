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

	// level
	'depth' => array(
		'type' => 'numberfield',
		'value' => 10,
	),

	// startId
	// includeDocs
	// excludeDocs
	'parents' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// displayStart
	'includeParents' => array(
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
	// contexts
	'context' => array(
		'type' => 'textfield',
		'value' => '',
	),

	// cacheResults
	/*
	'cache' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'cacheTime' => array(
		'type' => 'numberfield',
		'value' => '',
	),
	*/

	// ph
	'plPrefix' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// ignoreHidden
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
		'value' => true,
	),
	'hideSubMenus' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),

	'useWeblinkUrl' => array(
		'type' => 'combo-boolean',
		'value' => true,
	),

	// sortOrder
	'sortdir' => array(
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		),
		'value' => 'ASC'
	),
	// sortBy
	'sortby' => array(
		'type' => 'textfield',
		'value' => 'menuindex',
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 10,
	),

	// cssTpl
	// jsTpl
	// rowIdPrefix
	// textOfLinks
	// titleOfLinks

	'firstClass' => array(
		'type' => 'textfield',
		'value' => 'first',
	),
	'' => array(
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
		'value' => 'first',
	),
	'selfClass' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'webLinkClass' => array(
		'type' => 'textfield',
		'value' => '',
	),

	// outerTpl
	'tplOuter' => array(
		'type' => 'textfield',
		'value' => '@INLINE <ul[[+classes]]>[[+wrapper]]</ul>',
	),
	// rowTpl
	'tpl' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li[[+classes]]><a href="[[+link]]" title="" [[+attributes]]>[[+menutitle]]</a>[[+wrapper]]</li>',
	),
	// parentRowTpl
	'tplParentRow' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// parentRowHereTpl
	'tplParentRowHere' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// hereTpl
	'tplHere' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// innerTpl
	'tplInner' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// innerRowTpl
	'tplInnerRow' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// innerHereTpl
	'tplInnerHere' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// activeParentRowTpl
	'tplParentRowActive' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// categoryFoldersTpl
	'tplCategoryFolder' => array(
		'type' => 'textfield',
		'value' => '',
	),
	// startItemTpl
	/*
	'tplStart' => array(
		'type' => 'textfield',
		'value' => '@INLINE <h2[[+classes]]>[[+menutitle]]</h2>[[+wrapper]]',
	),
	*/

	// permissions
	'checkPermissions' => array(
		'type' => 'textfield',
		//'value' => 'load',
		'value' => '',
	),
	// hereId
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
		'value' => -1,
	),

	'toPlaceholder' => array(
		'type' => 'textfield',
		'value' => '',
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