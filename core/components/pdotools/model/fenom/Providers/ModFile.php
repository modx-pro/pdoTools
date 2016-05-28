<?php

class modFileProvider extends \Fenom\Provider
{
    /** @var modX $modx */
    public $modx;
    /** @var pdoTools $pdoTools */
    public $pdoTools;


    function __construct(pdoTools $pdoTools)
    {
        $dir = !file_exists($pdoTools->config['elementsPath'])
            ? MODX_CORE_PATH . 'cache/'
            : $pdoTools->config['elementsPath'];
        parent::__construct($dir);
        $this->pdoTools = $pdoTools;
        $this->modx = $pdoTools->modx;
    }

}