<?php
/**
 * Resolve creating db tables
 *
 * @subpackage build
 */

if ($object->xpdo) {
	/** @var $modx modX */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			//$modx->addExtensionPackage('pdotools', '[[++core_path]]components/pdotools/model/');
			$modx->removeExtensionPackage('pdotools');
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			$modx->removeExtensionPackage('pdotools');
			break;
	}
}
return true;