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
	,'name' => 'example.pdoFetch'
	,'description' => 'Example of pdoTools based snippet that returns all published resources. You can rename it and rewrite as you need.'
	,'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.pdofetch.php')
	,'source' => 1
	,'static' => 1
	,'static_file' => 'core/components/pdotools/elements/snippets/snippet.pdofetch.php'
),'',true,true);

$properties = include $sources['build'].'properties/properties.pdofetch.php';
$snippets[0]->setProperties($properties);

unset($properties);
return $snippets;