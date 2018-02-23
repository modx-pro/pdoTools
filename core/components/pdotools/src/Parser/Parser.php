<?php

namespace MODX\Components\PDOTools\Parser;

use xPDO\xPDO;
use MODX\Components\PDOTools\Core;
use MODX\Revolution\modResource;
use MODX\Revolution\modParser;

class Parser extends modParser
{
    /** @var Core $pdoTools */
    public $pdoTools;
    public $ignores = [];


    /**
     * @param xPDO $modx
     * @param Core $pdoTools
     */
    function __construct(xPDO &$modx, $pdoTools = null)
    {
        parent::__construct($modx);
        if (!$pdoTools) {
            $pdoTools = new Core($modx);
        }
        $this->pdoTools = $pdoTools;
    }


    /**
     * Trying to process MODX pages with Fenom template engine
     *
     * @param string $parentTag
     * @param string $content
     * @param bool $processUncacheable
     * @param bool $removeUnprocessed
     * @param string $prefix
     * @param string $suffix
     * @param array $tokens
     * @param int $depth
     *
     * @return int
     */
    public function processElementTags($parentTag, &$content, $processUncacheable = false, $removeUnprocessed = false, $prefix = '[[', $suffix = ']]', $tokens = [], $depth = 0)
    {
        if (is_string($content) && $processUncacheable && !empty($this->pdoTools->config['useFenomParser'])) {
            if ($this->pdoTools->debug->enabled && empty($parentTag) && preg_match($this->pdoTools->config['fenomSyntax'], $content)) {
                $logTag = htmlentities(trim($content), ENT_QUOTES, 'UTF-8');
                $this->pdoTools->debug->log($logTag);
            }

            if (preg_match_all('#{ignore}(.*?){ignore}#is', $content, $ignores)) {
                foreach ($ignores[1] as $ignore) {
                    $key = 'ignore_' . md5($ignore);
                    $this->ignores[$key] = $ignore;
                    $content = str_replace($ignore, $key, $content);
                }
            }
            $content = $this->pdoTools->fenom->process($content, $this->modx->placeholders);
        }

        $result = parent::processElementTags($parentTag, $content, $processUncacheable, $removeUnprocessed, $prefix, $suffix, $tokens, $depth);
        if (isset($logTag)) {
            $this->pdoTools->debug->log($logTag);
        }

        return $result;
    }


    /**
     * Quickly processes a simple tag and returns the result.
     *
     * @param string $tag A full tag string parsed from content.
     * @param boolean $processUncacheable
     *
     * @return mixed The output of the processed element represented by the specified tag.
     */
    public function processTag($tag, $processUncacheable = true)
    {
        $outerTag = $tag[0];
        $innerTag = $tag[1];
        $processed = false;
        $output = $token = '';
        if ($this->pdoTools->debug->enabled) {
            $logTag = $outerTag;
            $this->pdoTools->debug->log($logTag);
        }

        // Disabled tag
        if (empty($innerTag[0]) || $innerTag[0] == '-') {
            return '';
        } // Uncacheable tag
        elseif ($innerTag[0] == '!' && !$processUncacheable) {
            $this->processElementTags($outerTag, $innerTag, $processUncacheable);
            $outerTag = '[[' . $innerTag . ']]';
            if (isset($logTag)) {
                $this->pdoTools->debug->log($logTag);
            }

            return $outerTag;
        } // We processing only certain types of tags without parameters
        elseif (strpos($innerTag, '?') === false && preg_match('/^(?:!|)[-%~+*#]+/', $innerTag, $matches)) {
            if (strpos($innerTag, '[[') !== false) {
                $this->processElementTags($outerTag, $innerTag, $processUncacheable);
                $outerTag = '[[' . $innerTag . ']]';
            }

            $innerTag = ltrim($this->realname($innerTag), '!');
            $token = $innerTag[0];
            $innerTag = substr($innerTag, 1);
            switch ($token) {
                // Lexicon tag
                case '%':
                    $tmp = $this->modx->lexicon($innerTag);
                    if ($tmp != $innerTag) {
                        $output = $tmp;
                        $processed = true;
                    }
                    break;
                // Link tag
                case '~':
                    if (is_numeric($innerTag)) {
                        if ($tmp = $this->modx->makeUrl($innerTag, '', '', $this->modx->getOption('link_tag_scheme', null, -1, true))) {
                            $output = $tmp;
                            $processed = true;
                        }
                    }
                    break;
                // Usual placeholder
                // and
                // System setting
                case '+':
                    if (isset($this->modx->placeholders[$innerTag])) {
                        $output = $this->modx->placeholders[$innerTag];
                        $processed = true;
                    }
                    break;
                // Resource tag and TVs
                case '*':
                    if (is_object($this->modx->resource) && $this->modx->resource instanceof modResource) {
                        if ($innerTag == 'content') {
                            $output = $this->modx->resource->getContent();
                        } elseif (is_array($this->modx->resource->_fieldMeta) && isset($this->modx->resource->_fieldMeta[$innerTag])) {
                            $output = $this->modx->resource->get($innerTag);
                        } else {
                            $output = $this->modx->resource->getTVValue($innerTag);
                        }
                        $processed = true;
                    }
                    break;
                // FastField tag
                // Thanks to Argnist and Dimlight Studio (http://dimlight.ru) for the original idea
                case '#':
                    $processed = true;
                    $tmp = array_map('trim', explode('.', $innerTag));
                    $length = count($tmp);
                    // Resource tag
                    if (is_numeric($tmp[0])) {
                        /** @var modResource $resource */
                        if (!$resource = $this->pdoTools->getStore($tmp[0], 'resource')) {
                            $resource = $this->modx->getObject(modResource::class, $tmp[0]);
                            $this->pdoTools->setStore($tmp[0], $resource, 'resource');
                        }
                        $output = '';
                        if (!empty($resource)) {
                            // Field specified
                            if (!empty($tmp[1])) {
                                $tmp[1] = strtolower($tmp[1]);
                                if ($tmp[1] == 'content') {
                                    $output = $resource->getContent();
                                } // Resource field
                                elseif ($field = $resource->get($tmp[1])) {
                                    if (is_array($field) && $length > 2) {
                                        $tmp2 = array_slice($tmp, 2);
                                        $count = count($tmp2);
                                        foreach ($tmp2 as $k => $v) {
                                            if (isset($field[$v])) {
                                                if ($k == ($count - 1)) {
                                                    $output = $field[$v];
                                                } else {
                                                    $field = $field[$v];
                                                }
                                            }
                                        }
                                    } else {
                                        $output = $field;
                                    }
                                } // Template variable
                                elseif ($field === null) {
                                    unset($tmp[0]);
                                    $tmp = preg_replace('/^tv\./', '', implode('.', $tmp));
                                    $output = $resource->getTVValue($tmp);
                                }
                            } // No field specified - print the whole resource
                            else {
                                $output = $resource->toArray();
                            }
                        }
                    } // Global array tag
                    else {
                        switch (strtolower($tmp[0])) {
                            case 'post':
                                $array = $_POST;
                                break;
                            case 'get':
                                $array = $_GET;
                                break;
                            case 'request':
                                $array = $_REQUEST;
                                break;
                            case 'server':
                                $array = $_SERVER;
                                break;
                            case 'files':
                                $array = $_FILES;
                                break;
                            case 'cookie':
                                $array = $_COOKIE;
                                break;
                            case 'session':
                                $array = $_SESSION;
                                break;
                            default:
                                $array = [];
                                $processed = false;
                        }
                        // Field specified
                        if (!empty($tmp[1])) {
                            $field = isset($array[$tmp[1]])
                                ? $array[$tmp[1]]
                                : '';
                            $output = $field;
                            if (is_array($field)) {
                                if ($length > 2) {
                                    foreach ($tmp as $k => $v) {
                                        if ($k === 0) {
                                            continue;
                                        }
                                        if (isset($field[$v])) {
                                            $output = $field[$v];
                                        }
                                    }
                                }
                            }
                        } else {
                            $output = $array;
                        }
                        if (is_string($output)) {
                            $output = $this->modx->stripTags($output);
                        }
                    }
                    break;
            }
        }

        // Processing output filters
        if ($processed) {
            if (strpos($outerTag, ':') !== false) {
                /** @var Tag $object */
                $tag = new Tag($this->modx);
                $tag->_content = $output;
                $tag->setTag($outerTag);
                $tag->setToken($token);
                $tag->setContent(ltrim(rtrim($outerTag, ']'), '[!' . $token));
                $tag->setCacheable(!$processUncacheable);
                $tag->process();
                $output = $tag->_output;
            }
            if ($this->modx->getDebug() === true) {
                $this->modx->log(xPDO::LOG_LEVEL_DEBUG, "Processing {$outerTag} as {$innerTag}:\n" . print_r($output, 1) . "\n\n");
            }
            // Print array
            if (is_array($output)) {
                $output = htmlentities(print_r($output, true), ENT_QUOTES, 'UTF-8');
            }
        } else {
            $output = parent::processTag($tag, $processUncacheable);
        }

        if (isset($logTag)) {
            $this->pdoTools->debug->log($logTag);
        }

        return $output;
    }

}