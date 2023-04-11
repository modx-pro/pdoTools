<?php

namespace ModxPro\PdoTools\Parsing\Fenom;

use Exception;
use Fenom\Render;
use LogicException;
use ModxPro\PdoTools\CoreTools;
use ModxPro\PdoTools\Parsing\Fenom\Support\App;
use ModxPro\PdoTools\Parsing\Fenom\Providers\Chunk;
use ModxPro\PdoTools\Parsing\Fenom\Providers\File;
use ModxPro\PdoTools\Parsing\Fenom\Providers\Template;
use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use MODX\Revolution\modResource;
use MODX\Revolution\modLexicon;
use xPDO\xPDO;

class Fenom extends \Fenom
{
    /** @var modX $modx */
    protected $modx;
    /** @var CoreTools $pdoTools */
    protected $pdoTools;

    /**
     * Fenom constructor.
     *
     * @param modX $modx
     * @param CoreTools $pdoTools
     */
    public function __construct(modX $modx, CoreTools $pdoTools)
    {
        $this->pdoTools = $pdoTools;
        $this->modx = $modx;

        $provider = new Chunk($modx, $pdoTools);

        parent::__construct($provider);

        $this->setCompileDir(rtrim($pdoTools->config('cachePath'), '/') . '/file');
        $this->addProvider('template', new Template($modx, $pdoTools));
        $this->addProvider('file', new File($modx, $pdoTools));

        $default_options = [
            'disable_cache' => !$pdoTools->config('useFenomCache'),
            'force_compile' => !$pdoTools->config('useFenomCache'),
            'force_include' => !$pdoTools->config('useFenomCache'),
            'auto_reload' => $pdoTools->config('useFenomCache'),
        ];
        if ($options = json_decode($modx->getOption('pdotools_fenom_options'), true)) {
            $options = array_merge($default_options, $options);
        } else {
            $options = $default_options;
        }
        if (!$pdoTools->config('useFenomPHP')) {
            $this->removeAccessor('php');
            $options['disable_native_funcs'] = true;
        }
        $this->setOptions($options);

        $this->_addDefaultModifiers();

        $this->modx->invokeEvent(
            'pdoToolsOnFenomInit',
            [
                'fenom' => $this,
                'config' => $pdoTools->config(),
            ]
        );
    }

    /**
     * Parse content with Fenom syntax
     *
     * @param $chunk
     * @param array $properties
     * @return string
     */
    public function process($chunk, array $properties = [])
    {
        $content = is_array($chunk)
            ? trim($chunk['content'])
            : trim($chunk);
        if (empty($this->pdoTools->config('useFenom')) || !preg_match($this->pdoTools->config('fenomSyntax'), $content)) {
            return $content;
        }

        $name = '';
        if (is_array($chunk)) {
            if (!empty($chunk['binding'])) {
                $name = $chunk['binding'] . '/';
            }
            if (!empty($chunk['id'])) {
                $name .= $chunk['id'];
            } elseif (!empty($chunk['name'])) {
                $name .= $chunk['name'];
            } else {
                $name .= md5($content);
            }
        } else {
            $name = md5($content);
        }
        /** @var \Fenom\Template $tpl */
        if (!$tpl = $this->pdoTools->getStore($name, 'fenom')) {
            if (!empty($this->pdoTools->config('useFenomCache'))) {
                $cache_options = [
                    'cache_key' => 'pdotools/' . $name,
                ];
                if (!$cache = $this->pdoTools->getCache($cache_options)) {
                    if ($tpl = $this->_compileChunk($content, $name)) {
                        $this->pdoTools->setCache($tpl->getTemplateCode(), $cache_options);
                    }
                } else {
                    $cache = preg_replace('#^<\?php#', '', $cache);
                    $tpl = eval($cache);
                }
            } else {
                $tpl = $this->_compileChunk($content, $name);
            }
            if ($tpl) {
                $this->pdoTools->setStore($name, $tpl, 'fenom');
            }
        }

        if ($tpl instanceof Render) {
            $properties['_modx'] = new App($this->modx, $this->pdoTools);
            $properties['_pls'] = $properties;

            // Add system objects
            if (!empty($this->pdoTools->config('useFenomMODX'))) {
                $properties['modx'] = $this->modx;
                $properties['pdoTools'] = $this;
            }
            try {
                $content = $tpl->fetch($properties);
            } catch (Exception $e) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, $e->getMessage());
                $this->modx->log(modX::LOG_LEVEL_INFO, $tpl->getTemplateCode());
            }
        }

        return $content;
    }

    /**
     * Set compile directory
     *
     * @param string $dir directory to store compiled templates in
     *
     * @return \Fenom
     * @throws LogicException
     */
    public function setCompileDir($dir)
    {
        $dir = str_replace(MODX_CORE_PATH, '', $dir);
        $path = MODX_CORE_PATH;
        $tmp = explode('/', trim($dir, '/'));
        // FIX: use Flysystem
        foreach ($tmp as $v) {
            if (!empty($v)) {
                $path .= $v . '/';
            }
            if (!file_exists($path) && !mkdir($path) && !is_dir($path)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, sprintf('Directory "%s" was not created', $path));
            }
        }

        return parent::setCompileDir($path);
    }

    /**
     * Compiles Fenom chunk
     *
     * @param $content
     * @param string $name
     *
     * @return \Fenom\Template
     */
    protected function _compileChunk($content, $name = '')
    {
        if (empty($name)) {
            $name = md5($content);
        }
        try {
            $tpl = $this->getRawTemplate()->source($name, $content, true);
            $this->pdoTools->addTime('Compiled Fenom chunk with name "' . $name . '"');
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $e->getMessage());
            $this->modx->log(modX::LOG_LEVEL_INFO, $content);
            if ($this->modx->getOption('pdotools_fenom_save_on_errors')) {
                $this->pdoTools->setCache($content, ['cache_key' => 'error/' . $name]);
            }
            $tpl = $this->getRawTemplate()->source($name, '', false);
            $this->pdoTools->addTime('Can`t compile Fenom chunk with name "' . $name . '": ' . $e->getMessage());
        }

        return $tpl;
    }


    /**
     * Add default modifiers
     */
    protected function _addDefaultModifiers()
    {
        $modx = $this->modx;
        $pdo = $this->pdoTools;
        $fenom = $this;
        $micro = new App($modx, $pdo);

        // PHP Functions
        $this->_allowed_funcs = array_merge(
            $this->_allowed_funcs,
            [
                'rand' => 1,
                'number_format' => 1,
            ]
        );

        $this->_modifiers = array_merge(
            $this->_modifiers,
            [
                'md5' => 'md5',
                'sha1' => 'sha1',
                'crc32' => 'crc32',
                'urldecode' => 'urldecode',
                'urlencode' => 'urlencode',
                'rawurldecode' => 'rawurldecode',
                'base64_decode' => 'base64_decode',
                'base64_encode' => 'base64_encode',
                'http_build_query' => 'http_build_query',
                'print_r' => 'print_r',
                'var_dump' => 'var_dump',
                'dump' => 'var_dump',
                'nl2br' => 'nl2br',
                'ellipsis' => 'Fenom\Modifier::truncate',
                'len' => 'Fenom\Modifier::length',
                'length' => 'Fenom\Modifier::length',
                'strlen' => 'Fenom\Modifier::length',
                'number_format' => 'number_format',
                'number' => 'number_format',
                'reset' => 'reset',
                'end' => 'end',
            ]
        );

        // String Modifiers

        $this->_modifiers['lower'] =
        $this->_modifiers['low'] =
        $this->_modifiers['lcase'] =
        $this->_modifiers['lowercase'] =
        $this->_modifiers['strtolower'] = function ($string, $enc = 'utf-8') {
            return function_exists('mb_strtolower')
                ? mb_strtolower($string, $enc)
                : strtolower($string);
        };

        $this->_modifiers['upper'] =
        $this->_modifiers['up'] =
        $this->_modifiers['ucase'] =
        $this->_modifiers['uppercase'] =
        $this->_modifiers['strtoupper'] = function ($string, $enc = 'utf-8') {
            return function_exists('mb_strtoupper')
                ? mb_strtoupper($string, $enc)
                : strtoupper($string);
        };

        $this->_modifiers['ucwords'] = function ($string, $enc = 'utf-8') {
            return function_exists('mb_convert_case')
                ? mb_convert_case($string, MB_CASE_TITLE, $enc)
                : ucwords($string);
        };

        $this->_modifiers['ucfirst'] = function ($string, $enc = 'utf-8') {
            return function_exists('mb_strtoupper')
                ? mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc)
                . mb_strtolower(mb_substr($string, 1, null, $enc), $enc)
                : ucfirst($string);
        };

        $this->_modifiers['htmlent'] =
        $this->_modifiers['htmlentities'] = function ($string, $enc = 'utf-8') {
            return htmlentities($string, ENT_QUOTES, $enc);
        };

        $this->_modifiers['limit'] = function ($string, $limit = 100, $enc = 'utf-8') {
            $string = html_entity_decode($string, ENT_COMPAT, $enc);

            return function_exists('mb_substr')
                ? mb_substr($string, 0, $limit, $enc)
                : substr($string, 0, $limit);
        };

        $this->_modifiers['esc'] =
        $this->_modifiers['tag'] = function ($string) {
            $string = preg_replace('/&amp;(#[0-9]+|[a-z]+);/i', '&$1;', htmlspecialchars($string));

            return str_replace(
                ['[', ']', '`', '{', '}'],
                ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
                $string
            );
        };

        $this->_modifiers['notags'] =
        $this->_modifiers['striptags'] =
        $this->_modifiers['stripTags'] =
        $this->_modifiers['strip_tags'] = function ($string, $allowable_tags = null) {
            return strip_tags($string, $allowable_tags);
        };

        $this->_modifiers['stripmodxtags'] = function ($string) {
            return preg_replace("/\\[\\[([^\\[\\]]++|(?R))*?]]/s", '', $string);
        };

        $this->_modifiers['cdata'] = function ($string, $enc = 'utf-8') {
            if (function_exists('mb_strlen')) {
                $len = mb_strlen($string, $enc);
                if (mb_strpos($string, '[', 0, $enc) === 0) {
                    $string = ' ' . $string;
                }
                if (mb_strpos($string, ']', 0, $enc) === $len) {
                    $string = $string . ' ';
                }
            } else {
                $len = strlen($string);
                if (strpos($string, '[') === 0) {
                    $string = ' ' . $string;
                }
                if (strpos($string, ']') === $len) {
                    $string = $string . ' ';
                }
            }

            return "<![CDATA[{$string}]]>";
        };


        $this->_modifiers['reverse'] =
        $this->_modifiers['strrev'] = function ($string) {
            if (is_array($string)) {
                $string = array_reverse($string);
            } else {
                $ar = [];
                preg_match_all('/(\d+)?./us', $string, $ar);
                $string = join('', array_reverse($ar[0]));
            }

            return $string;
        };

        $this->_modifiers['wordwrap'] = function ($string, $width = null, $break = "<br />\n ") {
            if (!$width) {
                $width = 70;
            }

            return wordwrap($string, $width, $break, false);
        };

        $this->_modifiers['wordwrapcut'] = function ($string, $width = null, $break = "<br />\n ") {
            if (!$width) {
                $width = 70;
            }

            return wordwrap($string, $width, $break, true);
        };

        $this->_modifiers['fuzzydate'] = function ($date, $format = '%b %e') use ($modx) {
            $output = '&mdash;';

            if (!empty($date)) {
                /** @var modLexicon $lexicon */
                if ($lexicon = $modx->services->get('lexicon')) {
                    $lexicon->load('filters');
                }
                $time = !is_numeric($date)
                    ? strtotime($date)
                    : $date;
                if ($time >= strtotime('today')) {
                    $output = $modx->lexicon('today_at', ['time' => strftime('%I:%M %p', $time)]);
                } elseif ($time >= strtotime('yesterday')) {
                    $output = $modx->lexicon('yesterday_at', ['time' => strftime('%I:%M %p', $time)]);
                } else {
                    $output = strftime($format, $time);
                }
            }

            return $output;
        };

        // Conditional Operators

        $this->_modifiers['ismember'] =
        $this->_modifiers['memberof'] =
        $this->_modifiers['mo'] = function ($id, $groups = [], $matchAll = false) use ($modx, $pdo) {
            $pdo->debugParserModifier($id, 'ismember', $groups);
            if (is_string($groups)) {
                $groups = array_map('trim', explode(',', $groups));
            }

            /** @var $user modUser */
            if (empty($id)) {
                $id = $modx->user->get('id');
                $user = $modx->user;
            } else {
                $user = $modx->getObject(modUser::class, ['id' => (int)$id]);
            }
            $member = is_object($user) ? $user->isMember($groups, $matchAll) : false;
            $pdo->debugParserModifier($id, 'ismember', $groups);

            return $member;
        };

        $this->_modifiers['isloggedin'] = function ($ctx = null) use ($modx) {
            if (empty($ctx)) {
                $ctx = $modx->context->get('key');
            }

            return $modx->user->isAuthenticated($ctx);
        };

        $this->_modifiers['isnotloggedin'] = function ($ctx = null) use ($modx) {
            if (empty($ctx)) {
                $ctx = $modx->context->get('key');
            }

            return !$modx->user->isAuthenticated($ctx);
        };

        // Custom modifiers

        $this->_modifiers['declension'] =
        $this->_modifiers['decl'] = function ($amount, $variants, $number = false, $delimiter = '|') use ($modx) {
            $variants = explode($delimiter, $variants);
            if (count($variants) < 2) {
                $variants = array_fill(0, 3, $variants[0]);
            } elseif (count($variants) < 3) {
                $variants[2] = $variants[1];
            }
            $modulusOneHundred = $amount % 100;
            switch ($amount % 10) {
                case 1:
                    $text = $modulusOneHundred === 11
                        ? $variants[2]
                        : $variants[0];
                    break;
                case 2:
                case 3:
                case 4:
                    $text = ($modulusOneHundred > 10) && ($modulusOneHundred < 20)
                        ? $variants[2]
                        : $variants[1];
                    break;
                default:
                    $text = $variants[2];
            }

            return $number
                ? $amount . ' ' . $text
                : $text;
        };

        // MODX Functions

        $this->_modifiers['url'] = function ($id, $options = [], $args = []) use ($pdo) {
            $properties = array_merge($options, $args);
            $pdo->debugParserModifier($id, 'url', $properties);
            $url = $pdo->makeUrl($id, $options, $args);
            $pdo->debugParserModifier($id, 'url', $properties);

            return $url;
        };

        $this->_modifiers['lexicon'] = function ($key, $params = [], $language = '') use ($modx) {
            return $modx->lexicon($key, $params, $language);
        };

        $this->_modifiers['user'] =
        $this->_modifiers['userinfo'] = function ($id, $field = 'username') use ($modx, $pdo) {
            $pdo->debugParserModifier($id, 'user', $field);
            if (empty($id)) {
                $id = $modx->user->get('id');
            }
            $output = '';
            /** @var modUser $user */
            if ($user = $modx->getObjectGraph(modUser::class, '{"Profile":{}}', $id)) {
                $data = array_merge($user->toArray(), $user->Profile->toArray());
                unset($data['cachepwd'], $data['salt'], $data['sessionid'], $data['password'], $data['session_stale'], $data['remote_key'], $data['remote_data'], $data['hash_class']);

                if (strpos($field, 'extended.') === 0 && isset($data['extended'][substr($field, 9)])) {
                    $output = $data['extended'][substr($field, 9)];
                } elseif (strpos($field, 'remote_data.') === 0 && isset($data['remote_data'][substr($field, 12)])) {
                    $output = $data['remote_data'][substr($field, 12)];
                } elseif (isset($data[$field])) {
                    $output = $data[$field];
                }
            }
            $pdo->debugParserModifier($id, 'user', $field);

            return $output;
        };

        $this->_modifiers['resource'] = function ($id, $field = null) use ($pdo, $modx, $fenom) {
            $pdo->debugParserModifier($id, 'resource');
            /** @var modResource $resource */
            if (empty($id)) {
                $resource = $modx->resource;
            } elseif (!is_numeric($id)) {
                $field = $id;
                $resource = $modx->resource;
            } elseif (!$resource = $pdo->getStore($id, 'resource')) {
                $resource = $modx->getObject(modResource::class, ['id' => (int)$id]);
                $pdo->setStore($id, $resource, 'resource');
            }

            $output = '';
            if (!empty($resource)) {
                if (!empty($field)) {
                    if (strtolower($field) === 'content') {
                        $output = $resource->getContent();
                    } else {
                        $output = $resource->get($field);
                        if (is_null($output)) {
                            $output = $resource->getTVValue(preg_replace('#^tv\.#i', '', $field));
                        }
                    }
                } else {
                    $output = $resource->toArray();
                }
            }
            $pdo->debugParserModifier($id, 'resource');

            return $output;
        };

        $this->_modifiers['snippet'] = function ($name, $params = []) use ($pdo) {
            $pdo->debugParserModifier($name, 'snippet', $params);
            $result = $pdo->runSnippet($name, $params);
            $pdo->debugParserModifier($name, 'snippet', $params);

            return $result;
        };

        $this->_modifiers['chunk'] = function ($name, $params = []) use ($pdo) {
            $pdo->debugParserModifier($name, 'chunk', $params);
            $result = $pdo->getChunk($name, $params);
            $pdo->debugParserModifier($name, 'chunk', $params);

            return $result;
        };

        // Developer Functions

        $this->_modifiers['print'] = function ($var, $wrap = true) use ($fenom) {
            $output = print_r($var, true);
            $output = $fenom->_modifiers['esc']($output);
            if ($wrap) {
                $output = '<pre>' . $output . '</pre>';
            }

            return $output;
        };

        $this->_modifiers['setPlaceholder'] =
        $this->_modifiers['toPlaceholder'] = function ($value, $key) use ($modx) {
            $modx->toPlaceholder($key, $value);
        };

        $this->_modifiers['placeholder'] =
        $this->_modifiers['fromPlaceholder'] = function ($key) use ($modx) {
            return $modx->getPlaceholder($key);
        };

        $this->_modifiers['cssToHead'] = function ($string, $media = null) use ($micro) {
            $micro->regClientCSS($string, $media);
        };

        $this->_modifiers['htmlToHead'] = function ($string) use ($micro) {
            $micro->regClientStartupHTMLBlock($string);
        };

        $this->_modifiers['htmlToBottom'] = function ($string) use ($micro) {
            $micro->regClientHTMLBlock($string);
        };

        $this->_modifiers['jsToHead'] = function ($string, $plaintext = false) use ($micro) {
            $micro->regClientStartupScript($string, $plaintext);
        };

        $this->_modifiers['jsToBottom'] = function ($string, $plaintext = false) use ($micro) {
            $micro->regClientScript($string, $plaintext);
        };

        $this->_modifiers['json_encode'] =
        $this->_modifiers['toJSON'] = function ($array, $options = 0, $depth = 512) use ($modx) {
            return PHP_VERSION_ID < 50500
                ? json_encode($array, $options)
                : json_encode($array, $options, $depth);
        };

        $this->_modifiers['json_decode'] =
        $this->_modifiers['fromJSON'] = function ($string, $assoc = true, $depth = 512, $options = 0) use ($modx) {
            return PHP_VERSION_ID < 50400
                ? json_decode($string, $assoc, $depth)
                : json_decode($string, $assoc, $depth, $options);
        };

        $this->_modifiers['getOption'] =
        $this->_modifiers['option'] =
        $this->_modifiers['config'] = function ($key) use ($modx) {
            return $modx->getOption($key);
        };


        // PCRE Modifiers
        // Took from https://github.com/jasny/twig-extensions/blob/master/src/Jasny/Twig/PcreExtension.php

        $this->_modifiers['preg_quote'] = function ($value, $delimiter = '/') {
            return preg_quote($value, $delimiter);
        };

        $this->_modifiers['preg_match'] = function ($value, $pattern) {
            return preg_match($pattern, $value);
        };

        $this->_modifiers['preg_get'] = function ($value, $pattern, $group = 0) {
            if (!preg_match($pattern, $value, $matches)) {
                return null;
            }

            return $matches[$group] ?? null;
        };

        $this->_modifiers['preg_get_all'] = function ($value, $pattern, $group = 0) {
            if (!preg_match_all($pattern, $value, $matches, PREG_PATTERN_ORDER)) {
                return [];
            }

            return $matches[$group] ?? [];
        };

        $this->_modifiers['preg_grep'] = function ($value, $pattern, $flags = '') {
            if (is_string($flags)) {
                $flags = $flags === 'invert'
                    ? PREG_GREP_INVERT
                    : 0;
            }

            return preg_grep($pattern, $value, $flags);
        };

        $this->_modifiers['preg_replace'] = function ($value, $pattern, $replacement = '', $limit = -1) use ($fenom) {
            return preg_replace($pattern, $replacement, $value, $limit);
        };

        $this->_modifiers['preg_filter'] = function ($value, $pattern, $replacement = '', $limit = -1) use ($fenom) {
            return preg_filter($pattern, $replacement, $value, $limit);
        };

        $this->_modifiers['preg_split'] = function ($value, $pattern) use ($fenom) {
            return preg_split($pattern, $value);
        };

    }


    /**
     * Modifier autoloader
     *
     * @param string $name
     * @param \Fenom\Template $template
     *
     * @return callable
     */
    protected function _loadModifier($name, $template)
    {
        $pdo = $this->pdoTools;

        return function ($input, $options = null) use ($name, $pdo) {
            $pdo->debugParserModifier($input, $name, $options);
            $result = $pdo->runSnippet($name, [
                'input' => $input,
                'options' => $options,
                'pdoTools' => $pdo,
            ]);
            $pdo->debugParserModifier($input, $name, $options);

            return $result === false
                ? $input
                : $result;
        };
    }
}
