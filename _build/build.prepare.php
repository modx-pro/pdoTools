<?php

$root = dirname(__FILE__, 2) . '/';
require_once $root . '_build/includes/functions.php';
$base = $root . 'core/components/pdotools/vendor/fenom/fenom/';

// Clean base dir
if ($dirs = @scandir($base)) {
    foreach ($dirs as $dir) {
        if (!in_array($dir, ['src', 'config', 'vendor', '.', '..'])) {
            $path = $base . $dir;
            if (is_dir($path)) {
                removeDir($path);
            } else {
                unlink($path);
            }
        }
    }
}

// Clean vendors
// $base = $root . 'core/components/pdotools/vendor/fenom/fenom/vendor/';
// cleanPackages($base);
