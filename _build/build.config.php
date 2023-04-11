<?php

const PKG_NAME = 'pdoTools';
const PKG_NAME_LOWER = 'pdotools';

const PKG_VERSION = '3.0.2';
const PKG_RELEASE = 'pl';
const PKG_AUTO_INSTALL = false;

/* define paths */
if (isset($_SERVER['MODX_BASE_PATH'])) {
    define('MODX_BASE_PATH', $_SERVER['MODX_BASE_PATH']);
} elseif (file_exists(dirname(__DIR__, 2) . '/core')) {
    define('MODX_BASE_PATH', dirname(__DIR__, 2) . '/');
} else {
    define('MODX_BASE_PATH', dirname(__DIR__, 3) . '/');
}

const MODX_CORE_PATH = MODX_BASE_PATH . 'core/';
const MODX_MANAGER_PATH = MODX_BASE_PATH . 'manager/';
const MODX_CONNECTORS_PATH = MODX_BASE_PATH . 'connectors/';
const MODX_ASSETS_PATH = MODX_BASE_PATH . 'assets/';

/* define urls */
const MODX_BASE_URL = '/';
const MODX_CORE_URL = MODX_BASE_URL . 'core/';
const MODX_MANAGER_URL = MODX_BASE_URL . 'manager/';
const MODX_CONNECTORS_URL = MODX_BASE_URL . 'connectors/';
const MODX_ASSETS_URL = MODX_BASE_URL . 'assets/';

/* define build options */
//define('BUILD_MENU_UPDATE', false);
//define('BUILD_ACTION_UPDATE', false);
const BUILD_SETTING_UPDATE = false;
//define('BUILD_CHUNK_UPDATE', false);

const BUILD_SNIPPET_UPDATE = true;
const BUILD_PLUGIN_UPDATE = true;
const BUILD_EVENT_UPDATE = true;
//define('BUILD_POLICY_UPDATE', true);
//define('BUILD_POLICY_TEMPLATE_UPDATE', true);
//define('BUILD_PERMISSION_UPDATE', true);

//define('BUILD_CHUNK_STATIC', false);
const BUILD_SNIPPET_STATIC = false;
const BUILD_PLUGIN_STATIC = false;

$BUILD_RESOLVERS = [
    //'parser',
];
