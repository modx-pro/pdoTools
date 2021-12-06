<?php

namespace ModxPro\PdoTools\Parsing\Fenom\Support;

use MODX\Revolution\modX;
use MODX\Revolution\modCacheManager;

class CacheManager
{
    /** @var modX $modx */
    protected $modx;
    /** @var modCacheManager $cacheManager */
    protected $cacheManager;


    /**
     * @param modX $modx
     */
    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->cacheManager = $modx->getCacheManager();
    }


    /**
     * @param string $key
     * @param array $options
     *
     * @return mixed
     */
    public function get($key, $options = [])
    {
        return $this->cacheManager->get($key, $options);
    }


    /**
     * @param string $key
     * @param mixed $var
     * @param int $lifetime
     *
     * @return bool
     */
    public function set($key, &$var, $lifetime = 0)
    {
        // $options is not used due to security reasons
        return $this->cacheManager->set($key, $var, $lifetime);
    }

}
