<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo && !ini_get('safe_mode') && strpos(ini_get('disable_functions'), 'shell_exec') === false) {
    /** @var $modx modX */
    $modx =& $transport->xpdo;
    $path = MODX_BASE_PATH;
    $composer = $path . 'composer.phar';
    $params = "--working-dir {$path} --no-progress 2>&1";

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $message = shell_exec("php {$composer} require fenom/fenom:2.* {$params}");
            break;
        case xPDOTransport::ACTION_UPGRADE:
            $message = shell_exec("php {$composer} update fenom/fenom {$params}");
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $message = shell_exec("php {$composer} remove fenom/fenom {$params}");
            break;
    }
    $modx->log(modX::LOG_LEVEL_INFO, $message);
}

return true;