<?php

/** @var xPDOTransport $transport */

use MODX\Revolution\modSystemSetting;

/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    /** @var $modx modX */
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $settings = [
                'parser_class',
                'parser_class_path',
                'pdoTools.class',
                'pdoFetch.class',
                'pdoParser.class',
                'pdotools_class_path',
                'pdofetch_class_path',
            ];

            foreach ($settings as $key) {
                if ($setting = $modx->getObject(modSystemSetting::class, ['key' => $key])) {
                    $setting->remove();
                }
            }
            break;
    }
}

return true;