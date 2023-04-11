<?php

namespace ModxPro\PdoTools;

use xPDO\xPDO;
use MODX\Revolution\modX;
use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\modChunk;
use MODX\Revolution\modElement;
use MODX\Revolution\modScript;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modTemplateVar;
use MODX\Revolution\Sources\modFileMediaSource;
use ModxPro\PdoTools\Parsing\Fenom\Fenom;


class CoreTools
{
    /** @var modX $modx */
    protected $modx;
    /** @var Fenom $fenom */
    protected $fenom;
    /** @var array $config Array with class config */
    protected $config = [];
    /** @var array $store Array for cache elements and user data */
    protected $store = [
        'chunk' => [],
        'snippet' => [],
        'tv' => [],
        'data' => [],
        'resource' => [],
    ];
    /** @var int $idx Index of iterator of rows processing */
    public $idx = 1;
    /** @var array $timings Array with query log */
    protected $timings = [];
    /** @var int $time Time of script start */
    protected $time;
    /** @var int $count Total number of results for chunks processing */
    protected $count = 0;
    /** @var bool $preparing Specifies that now is the preparation */
    protected $preparing = false;
    /** @var int  */
    protected $start = 0;

    private $tags = [];


    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX $modx, array $config = [])
    {
        $this->time = $this->start = microtime(true);
        $this->modx = $modx;

        $this->setConfig($config);
    }


    /**
     * Set config from default values and given array.
     *
     * @param array $config
     * @param bool $clean_timings Clean timings array
     */
    public function setConfig(array $config = [], bool $clean_timings = true)
    {
        $this->config = array_merge([
            'fastMode' => false,
            'offset' => 0,

            'checkPermissions' => '',
            'loadModels' => '',
            'prepareSnippet' => '',
            'prepareTVs' => '',
            'processTVs' => '',

            'outputSeparator' => "\n",
            'decodeJSON' => true,
            'scheme' => '',
            'fenomSyntax' => $this->modx->getOption('pdotools_fenom_syntax', null, '#\{(\$|\/|\w+(\s|\(|\|)|\(|\')#', true),
        ], $config);
        $this->config['elementsPath'] = $this->modx->getOption('pdotools_elements_path', null, MODX_CORE_PATH . 'elements/', true);
        $this->config['cachePath'] = MODX_CORE_PATH . 'cache/pdotools';

        if ($clean_timings) {
            $this->timings = [];
        }

        if (empty($this->config['scheme'])) {
            $this->config['scheme'] = $this->modx->getOption('link_tag_scheme');
        }
        if (is_numeric($this->config['scheme'])) {
            $this->config['scheme'] = (int)$this->config['scheme'];
        }
        $this->config['useFenom'] = $this->modx->getOption('pdotools_fenom_default', null, true);
        $this->config['useFenomParser'] = $this->modx->getOption('pdotools_fenom_parser', null, false);
        $this->config['useFenomCache'] = $this->modx->getOption('pdotools_fenom_cache', null, false);
        $this->config['useFenomMODX'] = $this->modx->getOption('pdotools_fenom_modx', null, false);
        $this->config['useFenomPHP'] = $this->modx->getOption('pdotools_fenom_php', null, false);
        $this->config['filterPath'] = $this->modx->getOption('pdotools_filter_path', null, true);

        // Chunk file extensions
        $this->config['chunkExtensions'] = explode(',', str_replace(' ', '', $this->modx->getOption('pdotools_file_chunk_extensions', null, 'html,tpl')));
        // Prepare paths
        $pl = [
            'core_path' => MODX_CORE_PATH,
            'assets_path' => MODX_ASSETS_PATH,
            'base_path' => MODX_BASE_PATH,
        ];
        $pl1 = $this->makePlaceholders($pl, '', '{', '}', false);
        $pl2 = $this->makePlaceholders($pl, '', '[[+', ']]', false);
        foreach (['elementsPath', 'cachePath'] as $k) {
            $this->config[$k] = str_replace($pl1['pl'], $pl1['vl'],
                str_replace($pl2['pl'], $pl2['vl'], $this->config[$k])
            );
        }
    }

    /**
     * @param string|array|null $key Key to get or array to set.
     * @param mixed $default
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        if (null === $key) {
            return $this->config;
        }
        if (is_array($key)) {
            $this->config = array_merge($this->config, $key);
            return $this;
        }
        return $this->config[$key] ?? $default;
    }

    /**
     * @return Fenom
     */
    public function getFenom()
    {
        if (!$this->modx->services->has('fenom')) {
            $class = $this->modx->getOption('pdotools_fenom_class', null, Fenom::class, true);
            $this->modx->services->add('fenom', new $class($this->modx, $this));
        }

        return $this->modx->services->get('fenom');
    }

    /**
     * Add new record to time log
     *
     * @param $message
     * @param null $delta
     */
    public function addTime($message, $delta = null)
    {
        $time = microtime(true);
        if (!$delta) {
            $delta = $time - $this->time;
        }

        $this->timings[] = [
            'time' => number_format(round(($delta), 7), 7),
            'message' => $message,
        ];
        $this->time = $time;
    }


    /**
     * Return timings log
     *
     * @param bool $string Return array or formatted string
     *
     * @return array|string
     */
    public function getTime($string = true)
    {
        $this->timings[] = [
            'time' => number_format(round(microtime(true) - $this->start, 7), 7),
            'message' => '<b>Total time</b>',
        ];
        $this->timings[] = [
            'time' => number_format(round((memory_get_usage(true)), 2), 0, ',', ' '),
            'message' => '<b>Memory usage</b>',
        ];

        if (!$string) {
            return $this->timings;
        }

        $res = '';
        foreach ($this->timings as $v) {
            $res .= $v['time'] . ': ' . $v['message'] . "\n";
        }

        return $res;
    }


    /**
     * Set data to cache
     *
     * @param string $name
     * @param mixed $object
     * @param string $type
     */
    public function setStore($name, $object, $type = 'data')
    {
        $this->store[$type][$name] = $object;
    }


    /**
     * Get data from cache
     *
     * @param string $name
     * @param string $type
     *
     * @return mixed|null
     */
    public function getStore($name, $type = 'data')
    {
        return $this->store[$type][$name] ?? null;
    }


    /**
     * Loads specified list of packages models
     */
    public function loadModels()
    {
        if (empty($this->config['loadModels'])) {
            return;
        }

        $time = microtime(true);
        $models = [];
        if (strpos(ltrim($this->config['loadModels']), '{') === 0) {
            $tmp = json_decode($this->config['loadModels'], true);
            foreach ($tmp as $k => $v) {
                if (!is_array($v)) {
                    $v = [
                        'path' => trim($v),
                    ];
                }
                $v = array_merge([
                    'path' => MODX_CORE_PATH . 'components/' . strtolower($k) . '/model/',
                    'prefix' => null,
                ], $v);
                if (strpos($v['path'], MODX_CORE_PATH) === false) {
                    $v['path'] = MODX_CORE_PATH . ltrim($v['path'], '/');
                }
                $models[$k] = $v;
            }
        } else {
            $tmp = array_map('trim', explode(',', $this->config['loadModels']));
            foreach ($tmp as $v) {
                $parts = explode(':', $v, 2);
                $models[$parts[0]] = [
                    'path' => MODX_CORE_PATH . 'components/' . strtolower($parts[0]) . '/model/',
                    'prefix' => count($parts) > 1 ? $parts[1] : null,
                ];
            }
        }

        if (!empty($models)) {
            foreach ($models as $k => $v) {
                $t = '/' . str_replace([MODX_BASE_PATH, MODX_CORE_PATH], '', $v['path']);
                if ($this->modx->addPackage(strtolower($k), $v['path'], $v['prefix'])) {
                    $this->addTime('Loaded model "' . $k . '" from "' . $t . '"', microtime(true) - $time);
                } else {
                    $this->addTime('Could not load model "' . $k . '" from "' . $t . '"', microtime(true) - $time);
                }
                $time = microtime(true);
            }
        }

        $this->config['loadModels'] = '';
    }


    /**
     * Transform array to placeholders
     *
     * @param array $array
     * @param string $plPrefix
     * @param string $prefix
     * @param string $suffix
     * @param bool $uncacheable
     *
     * @return array
     */
    public function makePlaceholders(
        array $array = [],
        $plPrefix = '',
        $prefix = '[[+',
        $suffix = ']]',
        $uncacheable = true
    ) {
        $result = ['pl' => [], 'vl' => []];

        $uncached_prefix = str_replace('[[', '[[!', $prefix);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result = array_merge_recursive($result,
                    $this->makePlaceholders($v, $plPrefix . $k . '.', $prefix, $suffix, $uncacheable));
            } else {
                $pl = $plPrefix . $k;
                $result['pl'][$pl] = $prefix . $pl . $suffix;
                $result['vl'][$pl] = $v;
                if ($uncacheable) {
                    $result['pl']['!' . $pl] = $uncached_prefix . $pl . $suffix;
                    $result['vl']['!' . $pl] = $v;
                }
            }
        }

        return $result;
    }


    /**
     * Process and return the output from a snippet
     *
     * @param string $name The name of the snippet.
     * @param array $properties An associative array of properties to pass them as snippet parameters.
     *
     * @return mixed The processed output of the Snippet.
     */
    public function runSnippet($name, array $properties = [])
    {
        $name = trim($name);
        /** @var array $data */
        if (!empty($name)) {
            $data = $this->_loadElement($name, 'modSnippet', $properties);
        }
        if (empty($data) || !($data['object'] instanceof modSnippet)) {
            $this->modx->log(MODX_LOG_LEVEL_ERROR, "[pdoTools] Could not load snippet \"{$name}\"");

            return false;
        }

        $snippet = $data['object'];
        $snippet->_cacheable = $data['cacheable'];
        $snippet->_processed = false;
        $snippet->_propertyString = '';
        $snippet->_tag = '';
        if ($data['cacheable'] && !$this->modx->getParser()->isProcessingUncacheable()) {
            $scripts = ['jscripts', 'sjscripts', 'loadedjscripts'];
            $regScriptsBefore = $regScriptsAfter = [];
            foreach ($scripts as $prop) {
                $regScriptsBefore[$prop] = count($this->modx->$prop);
            }
            $output = $snippet->process(array_merge($data['properties'], $properties));
            foreach ($scripts as $prop) {
                $regScriptsAfter[$prop] = count($this->modx->$prop);
            }
            if (!empty($this->modx->resource)) {
                if ($regScriptsBefore['loadedjscripts'] < $regScriptsAfter['loadedjscripts']) {
                    foreach ($scripts as $prop) {
                        if ($regScriptsBefore[$prop] != $regScriptsAfter[$prop]) {
                            $resProp = '_' . $prop;
                            foreach (array_slice($this->modx->{$prop}, $regScriptsBefore[$prop]) as $key => $value) {
                                if (!is_array($this->modx->resource->{$resProp})) {
                                    $this->modx->resource->{$resProp} = [];
                                }

                                if ($prop == 'loadedjscripts') {
                                    $this->modx->resource->{$resProp}[$key] = $value;
                                } else {
                                    array_push($this->modx->resource->{$resProp}, $value);
                                }
                            }
                        }
                    }
                }
            }

            return $output;
        }

        return $snippet->process(array_merge($data['properties'], $properties));
    }


    /**
     * Process and return the output from a Chunk by name.
     *
     * @param string $name The name of the chunk.
     * @param array $properties An associative array of properties to process the Chunk with,
     * treated as placeholders within the scope of the Element.
     * @param bool $fastMode If false, all MODX tags in chunk will be processed.
     *
     * @return mixed The processed output of the Chunk.
     */
    public function getChunk($name = '', array $properties = [], $fastMode = false)
    {
        $properties = $this->prepareRow($properties);
        $name = trim($name);

        /** @var array $data */
        if (!empty($name)) {
            $data = $this->_loadElement($name, 'modChunk', $properties);
        }
        if (empty($name) || empty($data) || !($data['object'] instanceof modElement)) {
            return !empty($properties)
                ? str_replace(['[', ']', '`'], ['&#91;', '&#93;', '&#96;'],
                    htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'))
                : '';
        }

        $properties = array_merge($data['properties'], $properties);
        $content = $this->config['useFenom']
            ? $this->getFenom()->process($data, $properties)
            : $data['content'];

        // Processing given placeholders
        if ((strpos($content, '[[') !== false) && !empty($properties)) {
            $pl = $this->makePlaceholders($properties);
            $content = str_replace($pl['pl'], $pl['vl'], $content);
        }

        // Processing other placeholders
        if (strpos($content, '[[') !== false) {
            if ($fastMode) {
                $content = $this->fastProcess($content, true);
            } else {
                /** @var modChunk $chunk */
                $chunk = $data['object'];
                $chunk->_cacheable = false;
                $chunk->_processed = false;
                $chunk->_content = '';

                $content = $chunk->process($properties, $content);
            }
        }

        return $content;
    }


    /**
     * Parse a chunk using an associative array of replacement variables.
     *
     * @param string $name The name of the chunk.
     * @param array $properties An array of properties to replace in the chunk.
     * @param string $prefix The placeholder prefix, defaults to [[+.
     * @param string $suffix The placeholder suffix, defaults to ]].
     *
     * @return string The processed chunk with the placeholders replaced.
     */
    public function parseChunk($name = '', array $properties = [], $prefix = '[[+', $suffix = ']]')
    {
        $properties = $this->prepareRow($properties);
        $name = trim($name);

        /** @var array $chunk */
        if (!empty($name)) {
            $chunk = $this->_loadElement($name, 'modChunk', $properties);
        }
        if (empty($name) || empty($chunk['content'])) {
            return !empty($properties)
                ? str_replace(['[', ']', '`'], ['&#91;', '&#93;', '&#96;'],
                    htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'))
                : '';
        }

        $properties = array_merge($chunk['properties'], $properties);
        $content = $this->config['useFenom']
            ? $this->getFenom()->process($chunk, $properties)
            : $chunk['content'];

        if (strpos($content, '[[') !== false) {
            $pl = $this->makePlaceholders($properties, '', $prefix, $suffix);
            $content = str_replace($pl['pl'], $pl['vl'], $content);
        }

        return $content;
    }


    /**
     * Fast processing of MODX tags.
     *
     * @param string $content
     * @param bool $processUncacheable
     *
     * @return mixed
     */
    public function fastProcess($content, $processUncacheable = true)
    {
        $matches = [];
        $this->modx->getParser()->collectElementTags($content, $matches);

        $unprocessed = $pl = $vl = [];
        foreach ($matches as $tag) {
            $tmp = $this->modx->getParser()->processTag($tag, $processUncacheable);

            if ($tmp === $tag[0]) {
                $unprocessed[] = $tmp;
            } else {
                $pl[] = $tag[0];
                $vl[] = $tmp;
            }
        }

        $content = str_replace($pl, $vl, $content);
        $content = str_replace($unprocessed, '', $content);

        return $content;
    }


    /**
     * Method for define name of a chunk serving as resource template
     * This algorithm taken from snippet getResources by opengeek
     *
     * @param array $properties Resource fields
     *
     * @return mixed
     */
    public function defineChunk(array $properties = [])
    {
        $idx = isset($properties['idx']) ? (int)$properties['idx'] : $this->idx++;
        $idx -= $this->config['offset'];

        $first = empty($this->config['first']) ? ($this->config['offset'] + 1) : (int)$this->config['first'];
        $last = empty($this->config['last'])
            ? ($this->count + $this->config['offset'])
            : (int)$this->config['last'];

        $odd = !($idx & 1);
        $resourceTpl = '';
        if ($idx === $first && !empty($this->config['tplFirst'])) {
            $resourceTpl = $this->config['tplFirst'];
        } elseif ($idx === $last && !empty($this->config['tplLast'])) {
            $resourceTpl = $this->config['tplLast'];
        } elseif (!empty($this->config['tpl_' . $idx])) {
            $resourceTpl = $this->config['tpl_' . $idx];
        } elseif ($idx > 1) {
            $divisors = [];
            for ($i = $idx; $i > 1; $i--) {
                if (($idx % $i) === 0) {
                    $divisors[] = $i;
                }
            }
            if (!empty($divisors)) {
                foreach ($divisors as $divisor) {
                    if (!empty($this->config['tpl_n' . $divisor])) {
                        $resourceTpl = $this->config['tpl_n' . $divisor];
                        break;
                    }
                }
            }
        }

        if (empty($resourceTpl) && $odd && !empty($this->config['tplOdd'])) {
            $resourceTpl = $this->config['tplOdd'];
        } elseif (empty($resourceTpl) && !empty($this->config['tplCondition']) && !empty($this->config['conditionalTpls'])) {
            $conTpls = json_decode($this->config['conditionalTpls'], true);
            if (isset($properties[$this->config['tplCondition']])) {
                $subject = $properties[$this->config['tplCondition']];
                $tplOperator = !empty($this->config['tplOperator'])
                    ? strtolower($this->config['tplOperator'])
                    : '=';
                $tplCon = '';
                foreach ($conTpls as $operand => $conditionalTpl) {
                    switch ($tplOperator) {
                        case '!=':
                        case 'neq':
                        case 'not':
                        case 'isnot':
                        case 'isnt':
                        case 'unequal':
                        case 'notequal':
                            $tplCon = (($subject != $operand) ? $conditionalTpl : $tplCon);
                            break;
                        case '<':
                        case 'lt':
                        case 'less':
                        case 'lessthan':
                            $tplCon = (($subject < $operand) ? $conditionalTpl : $tplCon);
                            break;
                        case '>':
                        case 'gt':
                        case 'greater':
                        case 'greaterthan':
                            $tplCon = (($subject > $operand) ? $conditionalTpl : $tplCon);
                            break;
                        case '<=':
                        case 'lte':
                        case 'lessthanequals':
                        case 'lessthanorequalto':
                            $tplCon = (($subject <= $operand) ? $conditionalTpl : $tplCon);
                            break;
                        case '>=':
                        case 'gte':
                        case 'greaterthanequals':
                        case 'greaterthanequalto':
                            $tplCon = (($subject >= $operand) ? $conditionalTpl : $tplCon);
                            break;
                        case 'isempty':
                        case 'empty':
                            $tplCon = empty($subject) ? $conditionalTpl : $tplCon;
                            break;
                        case '!empty':
                        case 'notempty':
                        case 'isnotempty':
                            $tplCon = !empty($subject) && $subject != '' ? $conditionalTpl : $tplCon;
                            break;
                        case 'isnull':
                        case 'null':
                            $tplCon = $subject == null || strtolower($subject) == 'null'
                                ? $conditionalTpl
                                : $tplCon;
                            break;
                        case 'inarray':
                        case 'in_array':
                        case 'ia':
                            $operand = explode(',', $operand);
                            $tplCon = in_array($subject, $operand) ? $conditionalTpl : $tplCon;
                            break;
                        case 'between':
                        case 'range':
                        case '>=<':
                        case '><':
                            $operand = explode(',', $operand);
                            $tplCon = ($subject >= min($operand) && $subject <= max($operand))
                                ? $conditionalTpl
                                : $tplCon;
                            break;
                        case 'contains':
                            $tplCon = is_string($subject) &&
                                (strpos($subject, $operand) !== false ? $conditionalTpl : $tplCon);
                            break;
                        case '==':
                        case '=':
                        case 'eq':
                        case 'is':
                        case 'equal':
                        case 'equals':
                        case 'equalto':
                        default:
                            $tplCon = (($subject == $operand) ? $conditionalTpl : $tplCon);
                            break;
                    }
                }
            }
            if (!empty($tplCon)) {
                $resourceTpl = $tplCon;
            }
        }

        if (empty($resourceTpl) && !empty($this->config['tpl'])) {
            $resourceTpl = $this->config['tpl'];
        }

        return $resourceTpl;
    }


    /**
     * Loads and returns chunk by various methods.
     *
     * @param string $name Name or binding
     * @param string $type Type of element
     * @param array $row Current row with results being processed
     *
     * @return array|bool
     */
    protected function _loadElement($name, $type, $row = [])
    {
        $binding = $content = $propertySet = '';

        $name = trim($name);
        if (preg_match('#^!?@([A-Z]+)#', $name, $matches)) {
            $binding = $matches[1];
            $content = preg_replace('#^!?@' . $binding . '#', '', $name);
            $content = ltrim($content, ' :');
        }
        // Get property set
        if (!$binding && $pos = strpos($name, '@')) {
            $propertySet = substr($name, $pos + 1);
            $name = substr($name, 0, $pos);
        } elseif (in_array($binding, ['CHUNK', 'TEMPLATE', 'SNIPPET']) && $pos = strpos($content, '@')) {
            $propertySet = substr($content, $pos + 1);
            $content = substr($content, 0, $pos);
        }

        if ($type === 'modChunk' || $type === 'modTemplate') {
            // Replace inline tags in chunks
            $content = str_replace(['{{', '}}'], ['[[', ']]'], $content);

            // Change name for empty TEMPLATE binding so will be used template of given row
            if ($binding === 'TEMPLATE' && empty($content) && isset($row['template'])) {
                $name = '@TEMPLATE ' . $row['template'];
                $content = $row['template'];
            }
        }

        $cache_name = !empty($binding) && !in_array($binding, ['CHUNK', 'SNIPPET'])
            ? md5($name)
            : $name;
        if ($cache_name[0] === '!') {
            $cache_name = substr($cache_name, 1);
            $cacheable = false;
        } else {
            $cacheable = true;
        }
        // Load from cache
        $cache_key = !empty($propertySet)
            ? $cache_name . '@' . $propertySet
            : $cache_name;
        if ($element = $this->getStore($cache_key, $type)) {
            $element['cacheable'] = $cacheable && empty($binding);

            return $element;
        }

        $properties = [];
        /** @var modElement $element */
        switch ($binding) {
            case 'CODE':
            case 'INLINE':
                $element = $this->modx->newObject($type, ['name' => $cache_name]);
                if ($element instanceof modScript) {
                    if (empty($this->config['useFenomPHP']) || empty($this->config['useFenomMODX'])) {
                        $this->addTime('Could not create inline "' . $type . '" because of system settings.');

                        return false;
                    }
                    /** @var modScript $element */
                    $element->_scriptName = $element->getScriptName() . $cache_name;
                }
                $element->setContent($content);
                $this->addTime('Created inline "' . $type . '" with name "' . $cache_name . '"');
                $cacheable = false;
                break;
            case 'FILE':
                // TODO: Сделать несколько путей.
                $path = $this->config['elementsPath'];
                $filename = $content;
                if (strpos($path, MODX_BASE_PATH) === false && strpos($path, './') === 0) {
                    $path = MODX_BASE_PATH . $path;
                }
                $path = $this->config['filterPath'] ? preg_replace(["/\.*[\/|\\\]/i", "/[\/|\\\]+/i"], ['/', '/'], $path . $filename) : $path . $filename;
                //$rel_path = str_replace([MODX_BASE_PATH, MODX_CORE_PATH], '', $path);
                if (!file_exists($path)) {
                    $message = 'Could not find the element file "' . $content . '".';
                    $this->modx->log(MODX_LOG_LEVEL_ERROR, $message);
                    $this->addTime($message);

                    return false;
                }
                $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
                if (!$this->isAllowedFileExtension($fileExtension, $type)) {
                    $message = 'File extension "' . $fileExtension . '" is not allowed for element type ' . $type . '!';
                    $this->modx->log(MODX_LOG_LEVEL_ERROR, $message);
                    $this->addTime($message);

                    return false;
                }
                if ($content = file_get_contents($path)) {
                    $element = $this->modx->newObject($type, ['name' => $cache_name]);
                    $element->setContent($content);
                    $element->setProperties($properties);
                    if ($element instanceof modScript) {
                        /** @var modScript $element */
                        $element->_scriptName = $element->getScriptName() . $cache_name;
                    }
                    //$element->set('static', true);
                    //$element->set('static_file', $path);
                    $this->addTime('Created "' . $type . '" from file "' . $filename . '"');
                }
                $cacheable = false;
                break;
            case 'TEMPLATE':
                if ($type !== 'modSnippet') {
                    return $this->_loadElement($content, 'modTemplate', $row);
                }
                break;
            case 'CHUNK':
                if ($type === 'modChunk') {
                    return $this->_loadElement($content, 'modChunk', $row);
                }
                break;
            case 'SNIPPET':
                if ($type === 'modSnippet') {
                    return $this->_loadElement($content, 'modSnippet', $row);
                }
                break;
            default:
                $column = $type === 'modTemplate'
                    ? 'templatename'
                    : 'name';
                $c = filter_var($cache_name, FILTER_VALIDATE_INT) === false
                    ? [$column . ':=' => $cache_name]
                    : ['id:=' => $cache_name, 'OR:' . $column . ':=' => $cache_name];
                if ($element = $this->modx->getObject($type, $c)) {
                    $content = $element->getContent();
                    if (!empty($propertySet)) {
                        if ($tmp = $element->getPropertySet($propertySet)) {
                            $properties = $tmp;
                        }
                    } else {
                        $properties = $element->getProperties();
                    }
                    $this->addTime('Loaded "' . $type . '" with name "' . $cache_name . '"');
                }
        }

        if (!$element) {
            $this->addTime('Could not load or create "' . $type . '" with name "' . $name . '".');

            return false;
        }

        $data = [
            'object' => $element,
            'content' => $content,
            'properties' => $properties,
            'name' => $cache_name,
            'id' => (int)$element->get('id'),
            'binding' => strtolower($type),
            'cacheable' => $cacheable,
        ];
        $this->setStore($cache_key, $data, $type);

        return $data;
    }

    /**
     * Builds a hierarchical tree from given array
     *
     * @param array $tmp Array with rows
     * @param string $id Name of primary key
     * @param string $parent Name of parent key
     * @param array $roots Allowed roots of nodes
     *
     * @return array
     */
    public function buildTree($tmp = [], $id = 'id', $parent = 'parent', array $roots = [])
    {
        $time = microtime(true);

        if (empty($id)) {
            $id = 'id';
        }
        if (empty($parent)) {
            $parent = 'parent';
        }

        if (count($tmp) === 1) {
            $row = current($tmp);
            $tree = [
                $row[$parent] => [
                    'children' => [
                        $row[$id] => $row,
                    ],
                ],
            ];
        } else {
            $rows = $tree = [];
            foreach ($tmp as $v) {
                $rows[$v[$id]] = $v;
            }

            foreach ($rows as $rid => &$row) {
                if (empty($row[$parent]) || (!isset($rows[$row[$parent]]) && in_array($rid, $roots))) {
                    $tree[$rid] = &$row;
                } else {
                    $rows[$row[$parent]]['children'][$rid] = &$row;
                }
            }
        }

        $this->addTime('Tree was built', microtime(true) - $time);

        return $tree;
    }


    /**
     * Prepares fetched rows and process template variables
     *
     * @param array $rows
     *
     * @return array
     */
    public function prepareRows(array $rows = [])
    {
        $time = microtime(true);
        $prepare = $process = $prepareTypes = [];
        if (!empty($this->config['includeTVs']) && (!empty($this->config['prepareTVs']) || !empty($this->config['processTVs']))) {
            $tvs = array_map('trim', explode(',', $this->config['includeTVs']));
            $prepare = ($this->config['prepareTVs'] == 1)
                ? $tvs
                : array_map('trim', explode(',', $this->config['prepareTVs']));
            $prepareTypes = array_map('trim',
                explode(',', $this->modx->getOption('manipulatable_url_tv_output_types', null, 'image,file')));
            $process = ($this->config['processTVs'] == 1)
                ? $tvs
                : array_map('trim', explode(',', $this->config['processTVs']));

            $prepare = array_flip($prepare);
            $prepareTypes = array_flip($prepareTypes);
            $process = array_flip($process);
        }

        foreach ($rows as & $row) {
            // Extract JSON fields
            if ($this->config['decodeJSON']) {
                foreach ($row as $k => $v) {
                    if (!empty($v) && is_string($v) && ($v[0] === '[' || $v[0] === '{')) {
                        $tmp = json_decode($v, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $row[$k] = $tmp;
                        }
                    }
                }
            }

            // Prepare and process TVs
            if (!empty($tvs)) {
                foreach ($tvs as $tv) {
                    if (!isset($process[$tv]) && !isset($prepare[$tv])) {
                        continue;
                    }

                    /** @var modTemplateVar $templateVar */
                    if (!$templateVar = $this->getStore($tv, 'tv')) {
                        if ($templateVar = $this->modx->getObject('modTemplateVar', ['name' => $tv])) {
                            $sourceCache = isset($prepareTypes[$templateVar->type])
                                ? $templateVar->getSourceCache($this->modx->context->get('key'))
                                : null;
                            $templateVar->set('sourceCache', $sourceCache);
                            $this->setStore($tv, $templateVar, 'tv');
                        } else {
                            $this->addTime('Could not process or prepare TV "' . $tv . '"');
                            continue;
                        }
                    }

                    $tvPrefix = !empty($this->config['tvPrefix']) ? trim($this->config['tvPrefix']) : '';
                    $key = $tvPrefix . $templateVar->name;
                    if (isset($process[$tv])) {
                        $row[$key] = $templateVar->renderOutput($row['id']);
                    } elseif (isset($prepare[$tv]) && is_string($row[$key]) && strpos($row[$key],
                            '://') === false && method_exists($templateVar, 'prepareOutput')
                    ) {
                        if (isset($templateVar->sourceCache) && $source = $templateVar->sourceCache) {
                            if ($source['class_key'] === modFileMediaSource::class) {
                                if (!empty($source['baseUrl']) && !empty($row[$key])) {
                                    $row[$key] = $source['baseUrl'] . $row[$key];
                                    if (isset($source['baseUrlRelative']) && !empty($source['baseUrlRelative'])) {
                                        $row[$key] = $this->modx->context->getOption('base_url', null,
                                                MODX_BASE_URL) . $row[$key];
                                    }
                                }
                            } else {
                                $row[$key] = $templateVar->prepareOutput($row[$key]);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tvs)) {
            $this->addTime('Prepared and processed TVs', microtime(true) - $time);
        }

        return $rows;
    }


    /**
     * Allow user to prepare single row by custom snippet before render chunk
     * This method was developed in cooperation with Agel_Nash
     *
     * @param array $row
     *
     * @return array
     */
    public function prepareRow(array $row = [])
    {
        if ($this->preparing) {
            return $row;
        }

        if (!empty($this->config['prepareSnippet'])) {
            $this->preparing = true;
            $name = trim($this->config['prepareSnippet']);

            array_walk_recursive($row, function (&$value) {
                $value = str_replace(
                    ['[', ']', '{', '}'],
                    ['*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'],
                    $value
                );
            });

            $tmp = $this->runSnippet($name, array_merge($this->config, [
                'pdoTools' => $this,
                'pdoFetch' => $this,
                'row' => $row,
            ]));

            if (!is_array($tmp)) {
                $tmp = ($tmp[0] == '[' || $tmp[0] == '{')
                    ? json_decode($tmp, true)
                    : unserialize($tmp);
            }

            if (!is_array($tmp)) {
                $this->addTime('Preparation snippet must return an array, instead of "' . gettype($tmp) . '"');
            } else {
                $row = array_merge($row, $tmp);
            }
            $this->preparing = false;

            array_walk_recursive($row, function (&$value) {
                $value = str_replace(
                    ['*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'],
                    ['[', ']', '{', '}'],
                    $value
                );
            });
        }

        return $row;
    }


    /**
     * Checks user permissions to view the results
     *
     * @param array $rows
     *
     * @return array
     */
    public function checkPermissions(array $rows = [])
    {
        $permissions = [];
        if (!empty($this->config['checkPermissions'])) {
            $tmp = array_map('trim', explode(',', $this->config['checkPermissions']));
            foreach ($tmp as $v) {
                $permissions[$v] = true;
            }
        } else {
            return $rows;
        }
        $total = !empty($this->config['totalVar'])
            ? $this->modx->getPlaceholder($this->config['totalVar'])
            : 0;

        foreach ($rows as $key => $row) {
            /** @var modAccessibleObject $object */
            $object = $this->modx->newObject($this->config['class']);
            $object->_fields['id'] = $row['id'];
            if ($object instanceof modAccessibleObject && !$object->checkPolicy($permissions)) {
                unset($rows[$key]);
                $this->addTime($this->config['class'] . ' #' . $row['id'] . ' was excluded from results, because you do not have enough permissions');
                $total--;
            }
        }

        $this->addTime('Checked for permissions "' . implode(',', array_keys($permissions)) . '"');
        if (!empty($this->config['totalVar']) && !empty($this->config['setTotal'])) {
            $this->modx->setPlaceholder($this->config['totalVar'], $total);
        }

        return $rows;
    }


    /**
     * Returns data from cache
     *
     * @param mixed $options
     *
     * @return mixed
     */
    public function getCache($options = [])
    {
        $cacheKey = $this->getCacheKey($options);
        $cacheOptions = $this->getCacheOptions($options);

        $cached = '';
        if (!empty($cacheOptions) && !empty($cacheKey) && $this->modx->getCacheManager()) {
            if ($cached = $this->modx->cacheManager->get($cacheKey, $cacheOptions)) {
                $this->addTime('Retrieved data from cache "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
            } else {
                $this->addTime('No cached data for key "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
            }
        } else {
            $this->addTime('Could not check cached data for key "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
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
    public function setCache($data = [], $options = [])
    {
        $cacheKey = $this->getCacheKey($options);
        $cacheOptions = $this->getCacheOptions($options);

        if (!empty($cacheKey) && !empty($cacheOptions) && $this->modx->getCacheManager()) {
            $this->modx->cacheManager->set(
                $cacheKey,
                $data,
                $cacheOptions[xPDO::OPT_CACHE_EXPIRES],
                $cacheOptions
            );
            $this->addTime('Saved data to cache "' . $cacheOptions[xPDO::OPT_CACHE_KEY] . '/' . $cacheKey . '"');
        }

        return $cacheKey;
    }


    /**
     * @return bool
     */
    public function clearFileCache()
    {
        $count = 0;
        $dir = rtrim($this->config['cachePath'], '/') . '/file';
        if (is_dir($dir)) {
            $list = scandir($dir);
            foreach ($list as $file) {
                if ($file[0] === '.') {
                    continue;
                }
                if (is_file($dir . '/' . $file)) {
                    @unlink($dir . '/' . $file);
                    $count++;
                }
            }
        }

        return $count > 0;
    }


    /**
     * @param $id
     * @param array $options
     * @param array $args
     *
     * @return mixed|string
     */
    public function makeUrl($id, $options = [], $args = [])
    {
        $scheme = !empty($options['scheme'])
            ? $options['scheme']
            : $this->config['scheme'];
        if (strtolower($scheme) == 'uri' && !empty($options['uri'])) {
            $url = $options['uri'];
            if (!empty($args)) {
                if (is_array($args)) {
                    $args = rtrim(modX::toQueryString($args), '?&');
                }
                $url .= strpos($url, '?') !== false
                    ? '&'
                    : '?';
                $url .= ltrim(trim($args), '?&');
            }
        } else {
            if (!empty($options['context_key'])) {
                $context = $options['context_key'];
            } elseif (!empty($options['context'])) {
                $context = $options['context'];
            } else {
                $context = '';
            }
            if (strtolower($scheme) === 'uri') {
                $scheme = -1;
            }
            $url = $this->modx->makeUrl($id, $context, $args, $scheme, $options);
        }

        return $url;
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
            $options = $this->config;
        }

        $cacheOptions = [
            xPDO::OPT_CACHE_KEY => !empty($options['cache_key']) || !empty($options['cacheKey'])
                ? 'pdotools'
                : (!empty($this->modx->resource)
                    ? $this->modx->getOption('cache_resource_key', null, 'resource')
                    : 'pdotools'),

            xPDO::OPT_CACHE_HANDLER => !empty($options['cache_handler'])
                ? $options['cache_handler']
                : $this->modx->getOption('cache_resource_handler', null, 'xPDOFileCache'),

            xPDO::OPT_CACHE_EXPIRES => isset($options['cacheTime']) && $options['cacheTime'] !== ''
                ? (integer)$options['cacheTime']
                : (integer)$this->modx->getOption('cache_resource_expires', null, 0),
        ];

        return $cacheOptions;
    }


    /**
     * Returns key for cache of specified options
     *
     * @var mixed $options
     *
     * @return bool|string
     */
    protected function getCacheKey($options = [])
    {
        if (empty($options)) {
            $options = $this->config;
        }

        if (!empty($options['cache_key'])) {
            return $options['cache_key'];
        } elseif (!empty($options['cacheKey'])) {
            return $options['cacheKey'];
        }

        $key = !empty($this->modx->resource)
            ? $this->modx->resource->getCacheKey()
            : '';
        if (is_array($options)) {
            $options['cache_user'] = isset($options['cache_user'])
                ? (int)$options['cache_user']
                : $this->modx->user->id;
        }

        return $key . '/' . sha1(serialize($options));
    }


    /**
     * Flatten array of placeholders with nested arrays
     *
     * @param array $array
     * @param string $plPrefix
     *
     * @return array
     */
    protected function flattenArray(array $array, $plPrefix = '')
    {
        $result = [];

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result = array_merge($result, $this->flattenArray($v, $plPrefix . $k . '.'));
            } else {
                $result[$plPrefix . $k] = $v;
            }
        }

        return $result;
    }


    /**
     * Log Fenom modifier call
     *
     * @param $value
     * @param $filter
     * @param array $properties
     */
    public function debugParserModifier($value, $filter, $properties = [])
    {
        if (is_array($value)) {
            $value = trim(print_r($value, true));
        }
        if (!empty($properties)) {
            $properties = htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8');
            $tag = '{' . $value . ' | ' . $filter . ' : ' . $properties . '}';
        } else {
            $tag = '{' . $value . ' | ' . $filter . '}';
        }

        $this->debugParser($tag);
    }


    /**
     * Log Fenom method call
     *
     * @param string $method
     * @param string $name
     * @param array $properties
     */
    public function debugParserMethod($method, $name, array $properties = [])
    {
        if (is_array($name)) {
            $name = trim(print_r($name, true));
        }
        if (!empty($properties)) {
            $properties = htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8');
            $tag = '{$_modx->' . $method . '("' . $name . '", ' . $properties . ')}';
        } else {
            $tag = '{$_modx->' . $method . '("' . $name . '")}';
        }

        $this->debugParser($tag);
    }


    /**
     * Pass data to debugParser
     *
     * @param $tag
     */
    protected function debugParser($tag)
    {
        if ($this->modx->parser instanceof debugPdoParser) {
            /** @var debugPdoParser $parser */
            $parser = $this->modx->parser;
            $hash = sha1($tag);

            if (!isset($this->tags[$hash])) {
                $this->tags[$hash] = [
                    'queries' => $this->modx->executedQueries,
                    'queries_time' => $this->modx->queryTime,
                    'parse_time' => microtime(true),
                ];
            } else {
                $queries = $this->modx->executedQueries - $this->tags[$hash]['queries'];
                $queries_time = number_format(round($this->modx->queryTime - $this->tags[$hash]['queries_time'], 7), 7);
                $parse_time = number_format(round(microtime(true) - $this->tags[$hash]['parse_time'], 7), 7);
                if (!isset($parser->tags[$hash])) {
                    $parser->tags[$hash] = [
                        'tag' => $tag,
                        'attempts' => 1,
                        'queries' => $queries,
                        'queries_time' => $queries_time,
                        'parse_time' => $parse_time,
                    ];
                } else {
                    $parser->tags[$hash]['attempts'] += 1;
                    $parser->tags[$hash]['queries'] += $queries;
                    $parser->tags[$hash]['queries_time'] += $queries_time;
                    $parser->tags[$hash]['parse_time'] += $parse_time;
                }
                unset($this->tags[$hash]);
            }
        }
    }

    /**
     * @param string $extension
     * @param string $type
     * @return bool
     */
    private function isAllowedFileExtension($extension, $type = '')
    {
        switch ($type) {
            case 'modChunk':
                $result = in_array($extension, $this->config['chunkExtensions'], true);
                break;
            case 'modSnippet':
                $result = $extension === 'php';
                break;
            default:
                $result = false;
        }

        return $result;
    }
}
