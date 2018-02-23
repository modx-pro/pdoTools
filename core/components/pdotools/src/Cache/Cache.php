<?php

namespace MODX\Components\PDOTools\Cache;

use xPDO\xPDO;
use MODX\Components\PDOTools\Fetch;
use MODX\Components\PDOTools\Core;

class Cache
{
    /** @var Core|Fetch $modx */
    public $pdoTools;


    public function __construct(Core $pdoTools)
    {
        $this->pdoTools = $pdoTools;
    }


    /**
     * Returns data from cache
     *
     * @param mixed $options
     *
     * @return mixed
     */
    public function get($options = [])
    {
        $cacheKey = $this->getCacheKey($options);
        $cacheOptions = $this->getCacheOptions($options);

        $cached = '';
        if (!empty($cacheOptions) && !empty($cacheKey) && $this->pdoTools->modx->getCacheManager()) {
            if ($cached = $this->pdoTools->modx->cacheManager->get($cacheKey, $cacheOptions)) {
                $this->pdoTools->addTime('Retrieved data from cache "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
            } else {
                $this->pdoTools->addTime('No cached data for key "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
            }
        } else {
            $this->pdoTools->addTime('Could not check cached data for key "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
        }

        return $cached;
    }


    /**
     * Sets data to cache
     *
     * @param mixed $data
     * @param mixed $options
     *
     * @return string $cacheKey
     */
    public function set($data = [], $options = [])
    {
        $cacheKey = $this->getCacheKey($options);
        $cacheOptions = $this->getCacheOptions($options);

        if (!empty($cacheKey) && !empty($cacheOptions) && $this->pdoTools->modx->getCacheManager()) {
            $this->pdoTools->modx->cacheManager->set(
                $cacheKey,
                $data,
                $cacheOptions[xPDO::OPT_CACHE_EXPIRES],
                $cacheOptions
            );
            $this->pdoTools->addTime('Saved data to cache "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
        }

        return $cacheKey;
    }


    /**
     * @return bool
     */
    public function clear()
    {
        $count = 0;
        $dir = rtrim($this->pdoTools->config['cachePath'], '/') . '/file';
        if (is_dir($dir)) {
            $list = scandir($dir);
            foreach ($list as $file) {
                if ($file[0] == '.') {
                    continue;
                } elseif (is_file($dir . '/' . $file)) {
                    @unlink($dir . '/' . $file);
                    $count++;
                }
            }
        }

        return $count > 0;
    }


    /**
     * Returns array with options for cache
     *
     * @param $options
     *
     * @return array
     */
    protected function getCacheOptions($options = [])
    {
        if (empty($options)) {
            $options = $this->pdoTools->config;
        }

        return [
            xPDO::OPT_CACHE_KEY => !empty($options['cache_key']) || !empty($options['cacheKey'])
                ? 'default'
                : (!empty($this->pdoTools->modx->resource)
                    ? $this->pdoTools->modx->getOption('cache_resource_key', null, 'resource')
                    : 'default'),

            xPDO::OPT_CACHE_HANDLER => !empty($options['cache_handler'])
                ? $options['cache_handler']
                : $this->pdoTools->modx->getOption('cache_resource_handler', null, 'xPDOFileCache'),

            xPDO::OPT_CACHE_EXPIRES => isset($options['cacheTime']) && $options['cacheTime'] !== ''
                ? (integer)$options['cacheTime']
                : (integer)$this->pdoTools->modx->getOption('cache_resource_expires', null, 0),
        ];
    }


    /**
     * Returns key for cache of specified options
     *
     * @return bool|string
     * @var mixed $options
     *
     */
    protected function getCacheKey($options = [])
    {
        if (empty($options)) {
            $options = $this->pdoTools->config;
        }

        if (!empty($options['cache_key'])) {
            return $options['cache_key'];
        } elseif (!empty($options['cacheKey'])) {
            return $options['cacheKey'];
        }

        $key = !empty($this->pdoTools->modx->resource)
            ? $this->pdoTools->modx->resource->getCacheKey()
            : '';
        if (is_array($options)) {
            $options['cache_user'] = isset($options['cache_user'])
                ? (integer)$options['cache_user']
                : $this->pdoTools->modx->user->id;
        }

        return $key . '/' . sha1(serialize($options));
    }

}