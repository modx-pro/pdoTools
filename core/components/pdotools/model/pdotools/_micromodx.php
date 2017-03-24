<?php

/**
 * Class microMODX
 */
class microMODX
{
    /** @var modX $modx */
    protected $modx;
    /** @var pdoTools */
    protected $pdoTools;
    /** @var microMODXLexicon */
    public $lexicon;
    /** @var microMODXCacheManager */
    public $cacheManager;

    public $config = array();
    public $context = array();
    public $resource = array();
    public $user = array();


    /**
     * @param pdoTools $pdoTools
     */
    function __construct(pdoTools $pdoTools)
    {
        $this->modx = $modx = $pdoTools->modx;
        $this->pdoTools = $pdoTools;
        $this->config = $modx->config;

        if ($modx->context) {
            $this->context = $modx->context->toArray();
        }
        if ($modx->resource) {
            $this->resource = $modx->resource->toArray();
            $this->resource['content'] = $modx->resource->getContent();
            // TV parameters
            foreach ($this->resource as $k => $v) {
                if (is_array($v) && !empty($v[0]) && $k == $v[0]) {
                    $this->resource[$k] = $modx->resource->getTVValue($k);
                }
            }
        }
        if ($modx->user) {
            $this->user = $modx->user->toArray();
            /** @var modUserProfile $profile */
            if ($profile = $modx->user->getOne('Profile')) {
                $tmp = $profile->toArray();
                unset($tmp['id']);
                $this->user = array_merge($this->user, $tmp);
            }
        }

        $this->lexicon = new microMODXLexicon($modx);
        $this->cacheManager = new microMODXCacheManager($modx);
    }


    /**
     * @param $key
     * @param array $params
     * @param string $language
     *
     * @return null|string
     */
    public function lexicon($key, $params = array(), $language = '')
    {
        return $this->modx->lexicon($key, $params, $language);
    }


    /**
     * @param $name
     * @param array $placeholders
     *
     * @return string
     */
    public function getChunk($name, array $placeholders = array())
    {
        $this->pdoTools->debugParserMethod('getChunk', $name, $placeholders);
        $result = $this->pdoTools->getChunk($name, $placeholders);
        $this->pdoTools->debugParserMethod('getChunk', $name, $placeholders);

        return $result;
    }


    /**
     * @param $name
     * @param $placeholders
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    public function parseChunk($name, $placeholders, $prefix = '[[+', $suffix = ']]')
    {
        $this->pdoTools->debugParserMethod('parseChunk', $name, $placeholders);
        $result = $this->pdoTools->parseChunk($name, $placeholders, $prefix, $suffix);
        $this->pdoTools->debugParserMethod('parseChunk', $name, $placeholders);

        return $result;
    }


    /**
     * @param $name
     * @param array $params
     *
     * @return string
     */
    public function runSnippet($name, array $params = array())
    {
        $this->pdoTools->debugParserMethod('runSnippet', $name, $params);
        $output = $this->pdoTools->runSnippet($name, $params);
        $this->pdoTools->debugParserMethod('runSnippet', $name, $params);

        return $output;
    }


    /**
     * @param $id
     * @param string $context
     * @param string $args
     * @param int $scheme
     * @param array $options
     *
     * @return string
     */
    public function makeUrl($id, $context = '', $args = '', $scheme = -1, array $options = array())
    {
        $this->pdoTools->debugParserMethod('makeUrl', $id, $args);
        $result = $this->modx->makeUrl($id, $context, $args, $scheme, $options);
        $this->pdoTools->debugParserMethod('makeUrl', $id, $args);

        return $result;
    }


    /**
     * @param $name
     * @param $object
     * @param string $type
     */
    public function setStore($name, $object, $type = 'data')
    {
        $this->pdoTools->setStore($name, $object, $type);
    }


    /**
     * @param $name
     * @param string $type
     */
    public function getStore($name, $type = 'data')
    {
        $this->pdoTools->getStore($name, $type);
    }


    /**
     * @param $src
     * @param null $media
     * @param bool $cache
     */
    public function regClientCSS($src, $media = null, $cache = true)
    {
        if (empty($this->modx->config['fenom_sjscripts'])) {
            $this->modx->config['fenom_sjscripts'] = array();
        }
        $registered = count($this->modx->sjscripts);

        $this->modx->regClientCSS($src, $media);
        if (!$cache) {
            $this->modx->config['fenom_sjscripts'] = array_replace(
                $this->modx->config['fenom_sjscripts'],
                array_slice($this->modx->sjscripts, $registered, null, true)
            );
            if (empty($this->modx->config['fenom_loadedscripts'])) {
                $this->modx->config['fenom_loadedscripts'] = array();
            }
            $this->modx->config['fenom_loadedscripts'][$src] = true;
        }
    }


    /**
     * @param $src
     * @param bool|false $plaintext
     * @param bool $cache
     */
    public function regClientStartupScript($src, $plaintext = false, $cache = true)
    {
        if (empty($this->modx->config['fenom_sjscripts'])) {
            $this->modx->config['fenom_sjscripts'] = array();
        }
        $registered = count($this->modx->sjscripts);

        $this->modx->regClientStartupScript($src, $plaintext);

        if (!$cache) {
            $this->modx->config['fenom_sjscripts'] = array_replace(
                $this->modx->config['fenom_sjscripts'],
                array_slice($this->modx->sjscripts, $registered, null, true)
            );
            if (empty($this->modx->config['fenom_loadedscripts'])) {
                $this->modx->config['fenom_loadedscripts'] = array();
            }
            $this->modx->config['fenom_loadedscripts'][$src] = true;
        }
    }


    /**
     * @param $src
     * @param bool|false $plaintext
     * @param bool $cache
     */
    public function regClientScript($src, $plaintext = false, $cache = true)
    {
        if (empty($this->modx->config['fenom_jscripts'])) {
            $this->modx->config['fenom_jscripts'] = array();
        }
        $registered = count($this->modx->jscripts);

        $this->modx->regClientScript($src, $plaintext);

        if (!$cache) {
            $this->modx->config['fenom_jscripts'] = array_replace(
                $this->modx->config['fenom_jscripts'],
                array_slice($this->modx->jscripts, $registered, null, true)
            );
            if (empty($this->modx->config['fenom_loadedscripts'])) {
                $this->modx->config['fenom_loadedscripts'] = array();
            }
            $this->modx->config['fenom_loadedscripts'][$src] = true;
        }
    }


    /**
     * @param $html
     * @param bool $cache
     */
    public function regClientStartupHTMLBlock($html, $cache = true)
    {
        $this->regClientStartupScript($html, true, $cache);
    }


    /**
     * @param $html
     * @param bool $cache
     */
    public function regClientHTMLBlock($html, $cache = true)
    {
        $this->regClientScript($html, true, $cache);
    }


    /**
     * @param string $action
     * @param array $scriptProperties
     * @param array $options
     *
     * @return array
     */
    public function runProcessor($action = '', $scriptProperties = array(), $options = array())
    {
        $this->pdoTools->debugParserMethod('runProcessor', $action, $scriptProperties);
        /** @var modProcessorResponse $response */
        $response = $this->modx->runProcessor($action, $scriptProperties, $options);
        $this->pdoTools->debugParserMethod('runProcessor', $action, $scriptProperties);

        return array(
            'success' => !$response->isError(),
            'message' => $response->getMessage(),
            'response' => $response->getResponse(),
            'errors' => $response->getFieldErrors(),
        );
    }


    /**
     * @param $pm
     *
     * @return bool
     */
    public function hasPermission($pm)
    {
        return $this->modx->hasPermission($pm);
    }


    /**
     * @param string $sessionContext
     *
     * @return bool
     */
    public function isAuthenticated($sessionContext = 'web')
    {
        return $this->modx->user->isAuthenticated($sessionContext);
    }


    /**
     * @param string|array $groups Either a string of a group name or an array
     * @param bool|false $matchAll If true, requires the user to be a member of all
     * the groups specified. If false, the user can be a member of only one to
     *
     * @return bool
     */
    public function isMember($groups, $matchAll = false)
    {
        return $this->modx->user->isMember($groups, $matchAll);
    }


    /**
     * @param $context
     *
     * @return bool
     */
    public function hasSessionContext($context)
    {
        return $this->modx->user->hasSessionContext($context);
    }


    /**
     * @param $uri
     * @param string $context
     *
     * @return bool|int|mixed
     */
    public function findResource($uri, $context = '')
    {
        return $this->modx->findResource($uri, $context);
    }


    /**
     * @param string $type
     * @param array $options
     */
    public function sendError($type = '', $options = array())
    {
        $this->modx->sendError($type, $options);
    }


    /**
     * @param $url
     * @param bool|false $options
     * @param string $type
     * @param string $responseCode
     */
    public function sendRedirect($url, $options = false, $type = '', $responseCode = '')
    {
        $this->modx->sendRedirect($url, $options, $type, $responseCode);
    }


    /**
     * @param $id
     * @param null $options
     */
    public function sendForward($id, $options = null)
    {
        $this->modx->sendForward($id, $options);
    }


    /**
     * @param $key
     * @param $value
     */
    public function setPlaceholder($key, $value)
    {
        $this->modx->setPlaceholder($key, $value);
    }


    /**
     * @param $placeholders
     * @param string $namespace
     */
    public function setPlaceholders($placeholders, $namespace = '')
    {
        $this->modx->setPlaceholders($placeholders, $namespace);
    }


    /**
     * @param $subject
     * @param string $prefix
     * @param string $separator
     * @param bool|false $restore
     *
     * @return array
     */
    public function toPlaceholders($subject, $prefix = '', $separator = '.', $restore = false)
    {
        return $this->modx->toPlaceholders($subject, $prefix, $separator, $restore);
    }


    /**
     * @param $key
     * @param $value
     * @param string $prefix
     * @param string $separator
     * @param bool|false $restore
     *
     * @return array
     */
    public function toPlaceholder($key, $value, $prefix = '', $separator = '.', $restore = false)
    {
        return $this->modx->toPlaceholder($key, $value, $prefix, $separator, $restore);
    }


    /**
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->modx->placeholders;
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public function getPlaceholder($key)
    {
        return $this->modx->getPlaceholder($key);
    }


    /**
     * @param $key
     */
    public function unsetPlaceholder($key)
    {
        $this->modx->unsetPlaceholder($key);
    }


    /**
     * @param $keys
     */
    public function unsetPlaceholders($keys)
    {
        $this->modx->unsetPlaceholders($keys);
    }


    /**
     * @param null $id
     * @param int $depth
     * @param array $options
     *
     * @return array
     */
    public function getChildIds($id = null, $depth = 10, array $options = array())
    {
        return $this->modx->getChildIds($id, $depth, $options);
    }


    /**
     * @param null $id
     * @param int $height
     * @param array $options
     *
     * @return array
     */
    public function getParentIds($id = null, $height = 10, array $options = array())
    {
        return $this->modx->getParentIds($id, $height, $options);
    }


    /**
     * @param string $key
     * @param bool $string
     * @param string $tpl
     *
     * @return array|string
     */
    public function getInfo($key = '', $string = true, $tpl = '@INLINE {$key}: {$value}')
    {
        $totalTime = microtime(true) - $this->modx->startTime;
        $queryTime = sprintf("%2.4f s", $this->modx->queryTime);
        $totalTime = sprintf("%2.4f s", $totalTime);
        $phpTime = sprintf("%2.4f s", $totalTime - $queryTime);
        $queries = isset($this->modx->executedQueries) ? $this->modx->executedQueries : 0;
        $source = $this->modx->resourceGenerated ? 'database' : 'cache';

        $info = array(
            'queries' => $queries,
            'totalTime' => $totalTime,
            'queryTime' => $queryTime,
            'phpTime' => $phpTime,
            'source' => $source,
            'log' => "\n" . $this->pdoTools->getTime(),
        );

        if (empty($key) && !empty($string)) {
            $output = array();
            foreach ($info as $key => $value) {
                $output[] = $this->pdoTools->parseChunk($tpl, array(
                    'key' => $key,
                    'value' => $value,
                ));
            }

            return implode("\n", $output);
        } else {
            return !empty($key) && isset($info[$key])
                ? $info[$key]
                : $info;
        }
    }


    /**
     * @param $id
     * @param array $options
     *
     * @return array|bool
     */
    public function getResource($id, array $options = array())
    {
        if (!is_array($id) && is_numeric($id)) {
            $where = array(
                'id' => (int)$id,
            );
        } else {
            $where = $id;
        }
        $output = false;
        $this->pdoTools->debugParserMethod('getResource', $where, $options);
        /** @var pdoFetch $pdoFetch */
        if ($pdoFetch = $this->modx->getService('pdoFetch')) {
            $output = $pdoFetch->getArray('modResource', $where, $options);
        }
        $this->pdoTools->debugParserMethod('getResource', $where, $options);

        return $output;
    }


    /**
     * @param array $where
     * @param array $options
     *
     * @return array|bool
     */
    public function getResources($where, array $options = array())
    {
        $output = false;
        $this->pdoTools->debugParserMethod('getResources', $where, $options);
        /** @var pdoFetch $pdoFetch */
        if ($pdoFetch = $this->modx->getService('pdoFetch')) {
            $output = $pdoFetch->getCollection('modResource', $where, $options);
        }
        $this->pdoTools->debugParserMethod('getResources', $where, $options);

        return $output;
    }


    /**
     * @param string $alias
     *
     * @return string
     */
    public function cleanAlias($alias = '')
    {
        return modResource::filterPathSegment($this->modx, $alias);
    }

}


/**
 * Class microMODXLexicon
 */
class microMODXLexicon
{
    /** @var modX $modx */
    protected $modx;
    /** @var modLexicon $lexicon */
    protected $lexicon;


    /**
     * @param modX $modx
     */
    function __construct(modX $modx)
    {
        $this->modx = &$modx;
        $this->lexicon = $this->modx->getService('lexicon', 'modLexicon');
    }


    /**
     *
     */
    public function load()
    {
        $topics = func_get_args();

        foreach ($topics as $topic) {
            $this->lexicon->load($topic);
        }
    }

}


/**
 * Class microMODXCacheManager
 */
class microMODXCacheManager
{
    /** @var modX $modx */
    protected $modx;
    /** @var modCacheManager $cacheManager */
    protected $cacheManager;


    /**
     * @param modX $modx
     */
    function __construct(modX $modx)
    {
        $this->modx = &$modx;
        $this->cacheManager = $modx->getCacheManager();
    }


    /**
     * @param $key
     * @param array $options
     *
     * @return mixed
     */
    public function get($key, $options = array())
    {
        return $this->cacheManager->get($key, $options);
    }


    /**
     * @param $key
     * @param $var
     * @param int $lifetime
     *
     * @return bool
     */
    public function set($key, & $var, $lifetime = 0)
    {
        // $options is not used due to security reasons
        return $this->cacheManager->set($key, $var, $lifetime);
    }

}
