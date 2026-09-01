<?php

use MODX\Revolution\modSystemSetting;

if ($object->xpdo) {

    /** @var $modx modX */
    $modx = $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modSystemSetting $tmp */
            if (!$tmp = $modx->getObject(modSystemSetting::class, ['key' => 'modParser.class'])) {
                $tmp = $modx->newObject(modSystemSetting::class);
            }
            $tmp->fromArray([
                'namespace' => 'core',
                'area' => 'site',
                'xtype' => 'textfield',
                'value' => '\ModxPro\PdoTools\Parsing\Parser',
                'key' => 'modParser.class',
            ], '', true, true);
            $tmp->save();

        case xPDOTransport::ACTION_UNINSTALL:
            if ($tmp = $modx->getObject(modSystemSetting::class, ['key' => 'modParser.class', 'value' => '\MODX\Revolution\Parser'])) {
                $tmp->remove();
            }
    }
}
return true;