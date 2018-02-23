<?php

if (!class_exists('MODX\Components\PDOTools\Core')) {
    if (file_exists(MODX_CORE_PATH . 'components/pdotools/vendor/autoload.php')) {
        require_once MODX_CORE_PATH . 'components/pdotools/vendor/autoload.php';
    } else {
        return;
    }
}

use MODX\Components\PDOTools\Core;
use MODX\Components\PDOTools\Fetch;
use MODX\Components\PDOTools\Parser\Parser;
use MODX\Components\PDOTools\Cache\Cache;

/** @var modX $modx */
switch ($modx->event->name) {
    case 'OnMODXInit':
        $core = new Core($modx);
        $core->debug->enabled = $modx->user->hasSessionContext('mgr') && !empty($_REQUEST['debug']);
        $fetch = new Fetch($modx);

        $modx->services->add('pdotools', $core);
        $modx->services->add('pdoTools', $core);
        $modx->services->add('pdofetch', $fetch);
        $modx->services->add('pdoFetch', $fetch);
        $modx->services->add('parser', new Parser($modx, $core));
        $modx->parser = $modx->services->get('parser');
        break;

    case 'OnSiteRefresh':
        $cache = new Cache($modx->services->get('pdotools'));
        if ($cache->clear()) {
            $modx->log(modX::LOG_LEVEL_INFO, $modx->lexicon('refresh_default') . ': pdoTools');
        }
        break;

    case 'OnWebPageInit':
        /** @var Core $core */
        $core = $modx->services->get('pdotools');
        if ($core->debug->enabled && empty($_REQUEST['cache'])) {
            /** @var modResource $resource */
            if ($resource = $modx->getObject(modResource::class, $modx->resourceIdentifier)) {
                $resource->clearCache();
            }
        }
        break;

    case 'OnLoadWebPageCache':
        /** @var Core $core */
        $core = $modx->services->get('pdotools');
        if ($core->debug->enabled) {
            $core->debug->from_cache = true;
        }
        break;

    case 'OnWebPagePrerender':
        /** @var Parser $parser */
        $parser = $modx->getParser();
        if ($parser instanceof Parser) {
            foreach ($parser->ignores as $key => $val) {
                $modx->resource->_output = str_replace($key, $val, $modx->resource->_output);
            }
        }

        /** @var Core $core */
        $core = $modx->services->get('pdotools');
        if ($core->debug->enabled) {
            $data = $core->debug->getReport();
            $data['replace'] = empty($_REQUEST['add']) || strpos($modx->resource->_output, '</body>') === false;
            $data['top'] = !empty($_REQUEST['top'])
                ? $_REQUEST['top']
                : 0;
            $data['elementsPath'] = MODX_CORE_PATH . 'components/pdotools/elements/chunks/';

            $output = $core->getChunk('@FILE chunk.debug.tpl', $data);
            if ($data['replace']) {
                $modx->resource->_output = $output;
            } else {
                $modx->resource->_output .= $output;
            }
        }
        break;
}
