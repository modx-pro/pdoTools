<?php

use MODX\Revolution\modEvent;

$events = [];

$tmp = [
    'pdoToolsOnFenomInit',
];

foreach ($tmp as $k => $v) {
    /** @var modEvent $event */
    $event = $modx->newObject(modEvent::class);
    $event->fromArray([
        'name' => $v,
        'service' => 6,
        'groupname' => PKG_NAME,
    ], '', true, true);
    $events[] = $event;
}

return $events;