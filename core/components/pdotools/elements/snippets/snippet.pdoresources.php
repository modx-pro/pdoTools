<?php

use ModxPro\PdoTools\Fetch;
use MODX\Revolution\modSnippet;

/** @var array $scriptProperties */
/** @var \MODX\Revolution\modX $modx */

if (isset($parents) && $parents === '') {
    $scriptProperties['parents'] = $modx->resource->id;
}
if (!empty($returnIds)) {
    $scriptProperties['return'] = $return = 'ids';
} elseif (!isset($return)) {
    $scriptProperties['return'] = $return = 'chunks';
}

// Adding extra parameters into special place so we can put them in a results
/** @var modSnippet $snippet */
$additionalPlaceholders = $properties = [];
if (isset($this) && $this instanceof modSnippet && $this->get('properties')) {
    $properties = $this->get('properties');
} elseif ($snippet = $modx->getObject(modSnippet::class, ['name' => 'pdoResources'])) {
    $properties = $snippet->get('properties');
}
if (!empty($properties)) {
    foreach ($scriptProperties as $k => $v) {
        if (!isset($properties[$k])) {
            $additionalPlaceholders[$k] = $v;
        }
    }
}
$scriptProperties['additionalPlaceholders'] = $additionalPlaceholders;

$modx->services['pdotools_config'] = $scriptProperties;
$pdoFetch = $modx->services->get(Fetch::class);
$pdoFetch->addTime('pdoTools loaded');
$output = $pdoFetch->run();

if ($modx->user->isAuthenticated('mgr') && !empty($showLog)) {
    $modx->setPlaceholder('pdoResourcesLog', print_r($pdoFetch->getTime(), true));
}

// Return output
if (!empty($returnIds)) {
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
} elseif ($return === 'data') {
    return $output;
} elseif (!empty($toSeparatePlaceholders)) {
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, array_merge($additionalPlaceholders, ['output' => $output]),
            $pdoFetch->config('fastMode'));
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}
