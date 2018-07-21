<?php
/** @var modX $modx */
switch ($modx->event->name) {

    case 'OnMODXInit':
        $fqn = $modx->getOption('pdoTools.class', null, 'pdotools.pdotools', true);
        $path = $modx->getOption('pdotools_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
        $modx->loadClass($fqn, $path, false, true);

        $fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
        $path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
        $modx->loadClass($fqn, $path, false, true);
        break;

    case 'OnSiteRefresh':
        /** @var pdoTools $pdoTools */
        if ($pdoTools = $modx->getService('pdoTools')) {
            if ($pdoTools->clearFileCache()) {
                $modx->log(modX::LOG_LEVEL_INFO, $modx->lexicon('refresh_default') . ': pdoTools');
            }
        }
        break;

    case 'OnWebPagePrerender':
        $parser = $modx->getParser();
        if ($parser instanceof pdoParser) {
            foreach ($parser->pdoTools->ignores as $key => $val) {
                $modx->resource->_output = str_replace($key, $val, $modx->resource->_output);
            }
        }
        break;
}
