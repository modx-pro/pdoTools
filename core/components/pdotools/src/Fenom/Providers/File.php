<?php

namespace MODX\Components\PDOTools\Fenom\Providers;

use Fenom\Provider;
use MODX\Revolution\modX;
use MODX\Components\PDOTools\Core;

class File extends Provider
{
    /** @var modX $modx */
    public $modx;
    /** @var Core $pdoTools */
    public $pdoTools;


    function __construct(Core $pdoTools)
    {
        $dir = !file_exists($pdoTools->config['elementsPath'])
            ? MODX_CORE_PATH . 'cache/'
            : $pdoTools->config['elementsPath'];
        parent::__construct($dir);
        $this->pdoTools = $pdoTools;
        $this->modx = $pdoTools->modx;
    }

}