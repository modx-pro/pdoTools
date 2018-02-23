<?php

use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;

$plugins = [];

$tmp = [
    'pdoTools' => [
        'file' => 'pdotools',
        'description' => '',
        'events' => [
            'OnMODXInit' => -100,
            'OnSiteRefresh' => 0,
            'OnWebPagePrerender' => -100,
            'OnWebPageInit' => 0,
            'OnLoadWebPageCache' => 0,
        ],
    ],
];

foreach ($tmp as $k => $v) {
    /** @var modPlugin $plugin */
    $plugin = $modx->newObject(modPlugin::class);
    $plugin->fromArray([
        'name' => $k,
        'description' => @$v['description'],
        'plugincode' => getSnippetContent($sources['source_core'] . '/elements/plugins/plugin.' . $v['file'] . '.php'),
        'static' => BUILD_PLUGIN_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/plugin.' . $v['file'] . '.php',
    ], '', true, true);

    $events = [];
    if (!empty($v['events']) && is_array($v['events'])) {
        foreach ($v['events'] as $name => $priority) {
            /** @var $event modPluginEvent */
            $event = $modx->newObject(modPluginEvent::class);
            $event->fromArray([
                'event' => $name,
                'priority' => $priority,
                'propertyset' => 0,
            ], '', true, true);
            $events[] = $event;
        }
        unset($v['events']);
    }

    if (!empty($events)) {
        $plugin->addMany($events);
    }

    $plugins[] = $plugin;
}

return $plugins;