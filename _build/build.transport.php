<?php

use MODX\Revolution\Error\modError;
use MODX\Revolution\modX;
use MODX\Revolution\Transport\modPackageBuilder;
use MODX\Revolution\Transport\modTransportPackage;
use MODX\Revolution\modCategory;

/** @var \MODX\Revolution\modX $modx */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

require_once 'build.config.php';

/* define sources */
$root = dirname(__FILE__, 2) . '/';
$sources = [
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'snippets' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
    'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
    'validators' => $root . '_build/validators/',
    'resolvers' => $root . '_build/resolvers/',
];
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->services->add('error', new modError($modx));
$modx->error = $modx->services->get('error');

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');
$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

// create category
$category = $modx->newObject(modCategory::class);
$category->set('id', 1);
$category->set('category', PKG_NAME);
// create category vehicle
$attr = [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [],
];

// add snippets
if (defined('BUILD_SNIPPET_UPDATE')) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = [
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => BUILD_SNIPPET_UPDATE,
        xPDOTransport::UNIQUE_KEY => 'name',
    ];
    $snippets = include $sources['data'] . 'transport.snippets.php';
    if (!is_array($snippets)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in snippets.');
    } else {
        $category->addMany($snippets);
        $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' snippets.');
    }
}

// add plugins
if (defined('BUILD_PLUGIN_UPDATE')) {
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = [
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => BUILD_PLUGIN_UPDATE,
        xPDOTransport::UNIQUE_KEY => 'name',
    ];
    $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['PluginEvents'] = [
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => BUILD_PLUGIN_UPDATE,
        xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
    ];
    $plugins = include $sources['data'] . 'transport.plugins.php';
    if (!is_array($plugins)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in plugins.');
    } else {
        $category->addMany($plugins);
        $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($plugins) . ' plugins.');
    }
}

$attr[xPDOTransport::RELATED_OBJECTS] = !empty($attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]);
$vehicle = $builder->createVehicle($category, $attr);

// now pack in resolvers and validators
$vehicle->resolve('file', [
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
/** @noinspection PhpUndefinedVariableInspection */
foreach ($BUILD_RESOLVERS as $resolver) {
    if ($vehicle->resolve('php', ['source' => $sources['resolvers'] . 'resolve.' . $resolver . '.php'])) {
        $modx->log(modX::LOG_LEVEL_INFO, 'Added resolver "' . $resolver . '" to category.');
    } else {
        $modx->log(modX::LOG_LEVEL_INFO, 'Could not add resolver "' . $resolver . '" to category.');
    }
}
$builder->putVehicle($vehicle);

// load system settings
if (defined('BUILD_SETTING_UPDATE')) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
    } else {
        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => BUILD_SETTING_UPDATE,
        ];
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
    }
    unset($settings, $setting, $attributes);
}

// load plugins events
if (defined('BUILD_EVENT_UPDATE')) {
    $events = include $sources['data'] . 'transport.events.php';
    if (!is_array($events)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in events.');
    } else {
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => BUILD_EVENT_UPDATE,
        ];
        foreach ($events as $event) {
            $vehicle = $builder->createVehicle($event, $attributes);
            $builder->putVehicle($vehicle);
        }
        $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in ' . count($events) . ' Plugins events.');
    }
    unset ($events, $event, $attributes);
}

$builder->setPackageAttributes([
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'requires' => [
        'php' => '>=7.2.0',
        'modx' => '>=3.0.0-beta',
    ],
]);
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$signature = $builder->getSignature();
if (defined('PKG_AUTO_INSTALL') && PKG_AUTO_INSTALL) {
    $sig = explode('-', $signature);
    $versionSignature = explode('.', $sig[1]);

    /** @var modTransportPackage $package */
    if (!$package = $modx->getObject(modTransportPackage::class, ['signature' => $signature])) {
        $package = $modx->newObject(modTransportPackage::class);
        $package->set('signature', $signature);
        $package->fromArray([
            'created' => date('Y-m-d h:i:s'),
            'updated' => null,
            'state' => 1,
            'workspace' => 1,
            'provider' => 0,
            'source' => $signature . '.transport.zip',
            'package_name' => PKG_NAME,
            'version_major' => $versionSignature[0],
            'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
            'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
        ]);
        if (!empty($sig[2])) {
            $r = preg_split('/([0-9]+)/', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
            if (is_array($r) && !empty($r)) {
                $package->set('release', $r[0]);
                $package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
            } else {
                $package->set('release', $sig[2]);
            }
        }
        $package->save();
    }

    $package->xpdo->packages['MODX\Revolution\\'] = $package->xpdo->packages['Revolution'];
    if ($package->install()) {
        $modx->runProcessor('System/ClearCache');
    }
}
if (!empty($_GET['download'])) {
    echo '<script>document.location.href = "/core/packages/' . $signature . '.transport.zip' . '";</script>';
}

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Execution time: {$totalTime}\n");
echo '</pre>';