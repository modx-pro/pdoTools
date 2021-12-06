<?php

namespace ModxPro\PdoTools\Parsing\Fenom\Providers;

use Fenom\Provider;
use MODX\Revolution\modX;
use ModxPro\PdoTools\CoreTools;

class File extends Provider
{
    /** @var modX $modx */
    public $modx;
    /** @var CoreTools $pdoTools */
    public $pdoTools;

    public function __construct(modX $modx, CoreTools $pdoTools)
    {
        $dir = !file_exists($pdoTools->config('elementsPath'))
            ? MODX_CORE_PATH . 'cache/'
            : $pdoTools->config('elementsPath');

        parent::__construct($dir);

        $this->modx = $modx;
        $this->pdoTools = $pdoTools;
    }
}