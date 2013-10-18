<?php
/**
 * Add snippets to build
 *
 * @package pdotools
 * @subpackage build
 */
$snippets = array();

$tmp = array(
	'pdoResources' => 'pdoresources',
	'pdoUsers' => 'pdousers',
	'pdoCrumbs' => 'pdocrumbs',
	'pdoField' => 'pdofield',
	'pdoSitemap' => 'pdositemap',
	'pdoNeighbors' => 'pdoneighbors',
	'pdoPage' => 'pdopage',
	//'pdoMenu' => 'pdomenu',
);

foreach ($tmp as $k => $v) {
	/* @avr modSnippet $snippet */
	$snippet = $modx->newObject('modSnippet');
	$snippet->fromArray(array(
		'id' => 0
		,'name' => $k
		,'description' => ''
		,'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.'.$v.'.php')
		,'static' => BUILD_SNIPPET_STATIC
		,'source' => 1
		,'static_file' => 'core/components/'.PKG_NAME_LOWER.'/elements/snippets/snippet.'.$v.'.php'
		),'',true,true);

	$properties = include $sources['build'].'properties/properties.'.$v.'.php';
	$snippet->setProperties($properties);

	$snippets[] = $snippet;
}

unset($properties);
return $snippets;