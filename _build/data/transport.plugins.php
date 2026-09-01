<?php

/** @var \MODX\Revolution\modX $modx */

$plugins = [];

$tmp = [
    'pdoTools' => [
        'file' => 'pdotools',
        'description' => '',
        'events' => [
            'OnSiteRefresh' => 0,
            'OnWebPagePrerender' => -101,
        ],
    ],
];

foreach ($tmp as $k => $v) {
    /** @var modplugin $plugin */
    $plugin = $modx->newObject(MODX\Revolution\modPlugin::class);
    /** @noinspection PhpUndefinedVariableInspection */
    $plugin->fromArray(array(
        'name' => $k,
        'description' => @$v['description'],
        'plugincode' => getSnippetContent($sources['source_core'] . '/elements/plugins/plugin.' . $v['file'] . '.php'),
        'static' => BUILD_PLUGIN_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/plugin.' . $v['file'] . '.php',
    ), '', true, true);

    $events = array();
    if (!empty($v['events']) && is_array($v['events'])) {
        foreach ($v['events'] as $name => $priority) {
            /** @var $event MODX\Revolution\modPluginEvent */
            $event = $modx->newObject(MODX\Revolution\modPluginEvent::class);
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
