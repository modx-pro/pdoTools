<?php

if ($object->xpdo) {
    /** @var $modx modX */
    $modx =& $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            //$modx->addExtensionPackage('pdotools', '[[++core_path]]components/pdotools/model/');
            $modx->removeExtensionPackage('pdotools');
            $old_settings = array(
                //'pdoTools.class' => 'pdotools.pdotools',
                //'pdoFetch.class' => 'pdotools.pdofetch',
                'pdoParser.class' => 'pdotools.pdoparser',
                'pdotools_fenom_modifiers' => null,
            );
            foreach ($old_settings as $k => $v) {
                if ($item = $modx->getObject('modSystemSetting', array('key' => $k))) {
                    if (!$v || $item->get('value') == $v) {
                        $item->remove();
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            //$modx->removeExtensionPackage('pdotools');
            break;
    }
}
return true;