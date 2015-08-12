<?php
/* @var array $scriptProperties */
if (isset($parents) && $parents === '') {
	$scriptProperties['parents'] = $modx->resource->id;
}
if (!empty($returnIds)) {
	$scriptProperties['return'] = 'ids';
}

// Adding extra parameters into special place so we can put them in results
/** @var modSnippet $snippet */
$additionalPlaceholders = array();
if ($snippet = $modx->getObject('modSnippet', array('name' => 'pdoResources'))) {
	$properties = $snippet->get('properties');
	foreach ($scriptProperties as $k => $v) {
		if (!isset($properties[$k])) {
			$additionalPlaceholders[$k] = $v;
		}
	}
}
$scriptProperties['additionalPlaceholders'] = $additionalPlaceholders;

/* @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
	$pdoFetch = new $pdoClass($modx, $scriptProperties);
}
elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
	$pdoFetch = new $pdoClass($modx, $scriptProperties);
}
else {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoFetch from "MODX_CORE_PATH/components/pdotools/model/".');
	return false;
}
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
	}
	else {
		return $output;
	}
}
elseif (!empty($toSeparatePlaceholders)) {
	$output['log'] = $log;
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else {
	$output .= $log;

	if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
		$output = $pdoFetch->getChunk($tplWrapper, array_merge($additionalPlaceholders, array('output' => $output)), $pdoFetch->config['fastMode']);
	}

	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}
