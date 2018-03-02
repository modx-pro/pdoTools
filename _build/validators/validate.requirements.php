<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    /** @var $modx modX */
    $modx =& $transport->xpdo;
    $version = $modx->getVersionData();
    if ($version['version'] != 3) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'This package can be used only with MODX 3.');

        return false;
    }

    if (ini_get('safe_mode') || strpos(ini_get('disable_functions'), 'shell_exec') !== false) {
        if (!class_exists('Fenom')) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Could not install "fenom/fenom" with Composer, please do it manually and run install again.');

            return false;
        }

        return true;
    }

    $composer = MODX_BASE_PATH . 'composer.phar';
    if (!is_file($composer)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://getcomposer.org/composer.phar');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        file_put_contents($composer, $file = curl_exec($ch));
    }
    if (!is_file($composer) || !is_readable($composer)) {
        $modx->log(modX::LOG_LEVEL_ERROR, "Could not download Composer into {$composer}. Please do it manually.");

        return false;
    }
}

return true;