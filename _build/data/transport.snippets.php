<?php
/**
 * Add snippets to build
 * 
 * @package pdotools
 * @subpackage build
 */
$snippets = array();

$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
	'id' => 0
	,'name' => 'pdoGetResources'
	,'description' => 'Simple example of pdoTools based snippet.'
	,'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.pdo_get_resources.php')
	,'source' => 1
	,'static' => 1
	,'static_file' => 'core/components/pdotools/elements/snippets/snippet.pdo_get_resources.php'
),'',true,true);

$properties = include $sources['build'].'properties/properties.pdo_get_resources.php';
$snippets[0]->setProperties($properties);

unset($properties);
return $snippets;