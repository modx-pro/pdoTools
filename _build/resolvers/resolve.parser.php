<?php

if ($object->xpdo) {
    /** @var $modx modX */
    $modx =& $object->xpdo;

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

            // Remove old settings
            if ($tmp = $modx->getObject('modSystemSetting', array('key' => 'pdotools_useFenom'))) {
                $tmp->remove();
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            if ($tmp = $modx->getObject('modSystemSetting', array('key' => 'parser_class', 'value' => 'pdoParser'))) {
                $tmp->remove();
            }
            if ($tmp = $modx->getObject('modSystemSetting',
                array('key' => 'parser_class_path', 'value' => '{core_path}components/pdotools/model/pdotools/'))
            ) {
                $tmp->remove();
            }
            break;
    }
}
return true;