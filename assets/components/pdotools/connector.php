<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->services->add('error', new MODX\Revolution\Error\modError($modx));
$modx->error = $this->services->get('error');

// Switch context if needed
if (!empty($_REQUEST['pageId'])) {
    if ($resource = $modx->getObject(MODX\Revolution\modResource::class, ['id' => (int)$_REQUEST['pageId']])) {
        if ($resource->get('context_key') !== 'web') {
            $modx->switchContext($resource->get('context_key'));
        }
        $modx->resource = $resource;
    }
}

// Run snippet
if (!empty($_REQUEST['hash']) && !empty($_SESSION['pdoPage'][$_REQUEST['hash']])) {
    $scriptProperties = $_SESSION['pdoPage'][$_REQUEST['hash']];
    $_GET = $_POST;

    // For ClientConfig and other similar plugins
    $modx->invokeEvent('OnHandleRequest');

    $modx->runSnippet('pdoPage', $scriptProperties);
}
