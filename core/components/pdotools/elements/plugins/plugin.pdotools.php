<?php
/** @var \MODX\Revolution\modX $modx */

switch ($modx->event->name) {
    case 'OnSiteRefresh':
        /** @var ModxPro\PdoTools\CoreTools $coreTools */
        if ($coreTools = $modx->services->get('pdotools')) {
            if ($coreTools->clearFileCache()) {
                $modx->log(modX::LOG_LEVEL_INFO, $modx->lexicon('refresh_default') . ': pdoTools');
            }
        }
        break;
    case 'OnWebPagePrerender':
        /** @var ModxPro\PdoTools\Parsing\Parser $parser */
        $parser = $modx->getParser();
        if ($parser instanceof ModxPro\PdoTools\Parsing\Parser) {
            foreach ($parser->ignores as $key => $val) {
                $modx->resource->_output = str_replace($key, $val, $modx->resource->_output);
            }
        }
        break;
}