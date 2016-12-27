<?php
/**@var modX $modx */
define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

// Switch context if needed
if (!empty($_REQUEST['pageId'])) {
    if ($resource = $modx->getObject('modResource', (int)$_REQUEST['pageId'])) {
        if ($resource->get('context_key') != 'web') {
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
