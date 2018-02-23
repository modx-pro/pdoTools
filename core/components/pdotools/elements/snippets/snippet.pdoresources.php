<?php
/** @var array $scriptProperties */

use MODX\Components\PDOTools\Fetch;
use MODX\Revolution\modSnippet;

if (isset($parents) && $parents === '') {
    $scriptProperties['parents'] = $modx->resource->id;
}
if (!empty($returnIds)) {
    $scriptProperties['return'] = 'ids';
}

// Adding extra parameters into special place so we can put them in a results
/** @var modSnippet $snippet */
$additionalPlaceholders = $properties = [];
if (isset($this) && $this instanceof modSnippet) {
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

/** @var Fetch $pdoFetch */
$pdoClass = get_class($modx->services->get('pdofetch'));
$pdoFetch = new $pdoClass($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');
$output = $pdoFetch->run();

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $log .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($returnIds)) {
    $modx->setPlaceholder('pdoResources.log', $log);
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
} elseif (!empty($toSeparatePlaceholders)) {
    $output['log'] = $log;
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    $output .= $log;

    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, array_merge($additionalPlaceholders, ['output' => $output]),
            $pdoFetch->config['fastMode']);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}
