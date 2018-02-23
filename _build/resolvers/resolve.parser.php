<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    /** @var $modx modX */
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modSystemSetting $tmp */
            if (!$tmp = $modx->getObject('modSystemSetting', array('key' => 'parser_class'))) {
                $tmp = $modx->newObject('modSystemSetting');
            }
            $tmp->fromArray(array(
                'namespace' => 'pdotools',
                'area' => 'pdotools_main',
                'xtype' => 'textfield',
                'value' => 'pdoParser',
                'key' => 'parser_class',
            ), '', true, true);
            $tmp->save();

            /** @var modSystemSetting $tmp */
            if (!$tmp = $modx->getObject('modSystemSetting', array('key' => 'parser_class_path'))) {
                $tmp = $modx->newObject('modSystemSetting');
            }
            $tmp->fromArray(array(
                'namespace' => 'pdotools',
                'area' => 'pdotools_main',
                'xtype' => 'textfield',
                'value' => '{core_path}components/pdotools/model/pdotools/',
                'key' => 'parser_class_path',
            ), '', true, true);
            $tmp->save();
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $tmp = $modx->getObject('modSystemSetting', array(
                'key' => 'parser_class',
                'value' => 'pdoParser'
            ));
            if ($tmp) {
                $tmp->remove();
            }
            $tmp = $modx->getObject('modSystemSetting', array(
                'key' => 'parser_class_path',
                'value' => '{core_path}components/pdotools/model/pdotools/'
            ));
            if ($tmp) {
                $tmp->remove();
            }
            break;
    }
}
return true;