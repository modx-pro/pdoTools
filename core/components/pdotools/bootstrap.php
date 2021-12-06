<?php
/** @var MODX\Revolution\modX $modx */

require_once MODX_CORE_PATH . 'components/pdotools/vendor/autoload.php';

// Add factories
$modx->services[ModxPro\PdoTools\CoreTools::class] = $modx->services->factory(function ($c) use ($modx) {
    $class = $modx->getOption('pdotools_pdotools_class', null, ModxPro\PdoTools\CoreTools::class, true);
    $config = $c['pdotools_config'] ?? [];
    $c['pdotools_config'] = [];
    return new $class($modx, $config);
});
$modx->services[ModxPro\PdoTools\Fetch::class] = $modx->services->factory(function ($c) use ($modx) {
    $class = $modx->getOption('pdotools_fetch_class', null, ModxPro\PdoTools\Fetch::class, true);
    $config = $c['pdotools_config'] ?? [];
    $c['pdotools_config'] = [];
    return new $class($modx, $config);
});
$modx->services[ModxPro\PdoTools\Support\Paginator::class] = $modx->services->factory(function ($c) use ($modx) {
    $class = $modx->getOption('pdotools_paginator_class', null, ModxPro\PdoTools\Support\Paginator::class, true);
    $config = $c['pdotools_config'] ?? [];
    $c['pdotools_config'] = [];
    return new $class($modx, $config);
});
$modx->services[ModxPro\PdoTools\Support\MenuBuilder::class] = $modx->services->factory(function ($c) use ($modx) {
    $class = $modx->getOption('pdotools_menubuilder_class', null, ModxPro\PdoTools\Support\MenuBuilder::class, true);
    $config = $c['pdotools_config'] ?? [];
    $c['pdotools_config'] = [];
    return new $class($modx, $config);
});
// Add services
$modx->services->add('fenom', function ($c) use ($modx) {
    $class = $modx->getOption('pdotools_fenom_class', null, ModxPro\PdoTools\Parsing\Fenom\Fenom::class, true);
    return new $class($modx, $c->get('pdotools'));
});
$modx->services->add('parser', function ($c) use ($modx) {
    $class = $modx->getOption('modParser.class', null, ModxPro\PdoTools\Parsing\Parser::class, true);
    return new $class($modx, $c->get('pdotools'));
});
$modx->services->add('pdotools', function ($c) {
    return $c->get(ModxPro\PdoTools\CoreTools::class);
});
$modx->services->add('pdofetch', function ($c){
    return $c->get(ModxPro\PdoTools\Fetch::class);
});

