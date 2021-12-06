<?php

/** @var \MODX\Revolution\modX $modx */

$events = [];

$tmp = [
    'pdoToolsOnFenomInit',
];

foreach ($tmp as $k => $v) {
    /** @var MODX\Revolution\modEvent $event */
    $event = $modx->newObject(MODX\Revolution\modEvent::class);
    $event->fromArray([
        'name' => $v,
        'service' => 6,
        'groupname' => PKG_NAME,
    ], '', true, true);
    $events[] = $event;
}

return $events;