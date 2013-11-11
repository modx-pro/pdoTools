<?php

$properties = array();

$tmp = array(
	'plPrefix' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 10,
	),
	'maxLimit' => array(
		'type' => 'numberfield',
		'value' => 100,
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
		'value' => 'page',
	),
	'totalVar' => array(
		'type' => 'textfield',
		'value' => 'total',
	),
	'pageLimit' => array(
		'type' => 'numberfield',
		'value' => 5,
	),

	'element' => array(
		'type' => 'textfield',
		'value' => 'pdoResources',
	),

	'pageNavVar' => array(
		'type' => 'textfield',
		'value' => 'page.nav',
	),
	'tplPage' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li><a href="[[+href]]">[[+pageNo]]</a></li>',
	),
	'tplPageWrapper' => array(
		'type' => 'textfield',
		'value' => '@INLINE <div class="pagination"><ul>[[+first]][[+prev]][[+pages]][[+next]][[+last]]</ul></div>',
	),
	'tplPageActive' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="active"><a href="[[+href]]">[[+pageNo]]</a></li>',
	),
	'tplPageFirst' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a href="[[+href]]">[[%pdopage_first]]</a></li>',
	),
	'tplPageLast' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a href="[[+href]]">[[%pdopage_last]]</a></li>',
	),
	'tplPagePrev' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a href="[[+href]]">&laquo;</a></li>',
	),
	'tplPageNext' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><a href="[[+href]]">&raquo;</a></li>',
	),
	'tplPageSkip' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="disabled"><span>...</span></li>',
	),

	'tplPageFirstEmpty' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><span>[[%pdopage_first]]</span></li>',
	),
	'tplPageLastEmpty' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="control"><span>[[%pdopage_last]]</span></li>',
	),
	'tplPagePrevEmpty' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="disabled"><span>&laquo;</span></li>',
	),
	'tplPageNextEmpty' => array(
		'type' => 'textfield',
		'value' => '@INLINE <li class="disabled"><span>&raquo;</span></li>',
	),

	'cache' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'cacheTime' => array(
		'type' => 'numberfield',
		'value' => 3600,
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