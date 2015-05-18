<?php
switch ($modx->event->name) {

	case 'OnSiteRefresh':

		if ($pdoTools = $modx->getService('pdoTools')) {
			/** @var pdoTools $pdoTools */
			if ($pdoTools->clearCache()) {
				$modx->log(modX::LOG_LEVEL_INFO, $modx->lexicon('refresh_default') . ': pdoTools');
			}
		}
		break;

}