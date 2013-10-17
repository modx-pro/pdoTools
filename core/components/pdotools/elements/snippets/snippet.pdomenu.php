<?php
/** @var array $scriptProperties */
if (isset($parents) && $parents === '') {
	$scriptProperties['parents'] = $modx->resource->id;
}

/** @var pdoMenu $pdoMenu */
if (!$modx->loadClass('pdoMenu', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoMenu = new pdoMenu($modx, $scriptProperties);
$pdoMenu->addTime('pdoTools loaded');

$rows = $pdoMenu->run();
$tree = $pdoMenu->buildTree($rows);
$output = $pdoMenu->templateTree($tree);

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoMenuLog">' . print_r($pdoMenu->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}