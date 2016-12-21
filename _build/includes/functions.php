<?php

/**
 * @param $filename
 *
 * @return string
 */
function getSnippetContent($filename) {
    $file = trim(file_get_contents($filename));
    preg_match('#\<\?php(.*)#is', $file, $data);

    return rtrim(rtrim(trim($data[1]), '?>'));
}


/**
 * Recursive directory delete
 *
 * @param $dir
 */
function removeDir($dir) {
    $dir = rtrim($dir, '/');
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . '/' . $object)) {
                    removeDir($dir . '/' . $object);
                }
                else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}


/**
 * @param $base
 */
function cleanPackages($base) {
    if ($dirs = @scandir($base)) {
        foreach ($dirs as $dir) {
            if (in_array($dir, array('.', '..'))) {
                continue;
            }
            $path = $base . $dir;
            if (is_dir($path)) {
                if (in_array($dir, array('tests', 'docs', 'gui'))) {
                    removeDir($path);
                }
                else {
                    cleanPackages($path . '/');
                }
            }
            elseif (pathinfo($path, PATHINFO_EXTENSION) != 'php') {
                unlink($path);
            }
        }
    }
}