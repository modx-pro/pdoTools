<?php
/**
 * The base pdoTools snippet.
 *
 * @package pdotools
 */
$pdoFetch = $modx->getService('pdofetch','pdoFetch',$modx->getOption('pdotools.core_path',null,$modx->getOption('core_path').'components/pdotools/').'model/pdotools/',$scriptProperties);
if (!($pdoFetch instanceof pdoFetch)) return '';

$output = $pdoFetch->run();

if ($modx->user->hasSessionContext('mgr')) {
	$output .= '<pre>' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

return $output;