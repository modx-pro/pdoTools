<?php

namespace MODX\Components\PDOTools\Parser;

use MODX\Components\PDOTools\Core;
use MODX\Revolution\modX;
use MODX\Revolution\Processors\ProcessorResponse;
use xPDO\xPDO;

class Debug
{
    /** @var xPDO|modX */
    public $modx;
    /** @var Core $pdoTools */
    public $pdoTools;
    public $from_cache = false;
    public $enabled = false;
    protected $_tags = [];
    protected $tags = [];

    /**
     * @param Core $pdoTools
     */
    public function __construct(Core $pdoTools)
    {
        $this->pdoTools = $pdoTools;
        $this->modx = $pdoTools->modx;
    }


    /**
     * Log Fenom modifier call
     *
     * @param $value
     * @param $filter
     * @param array $properties
     */
    public function modifier($value, $filter, $properties = [])
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

        $this->log($tag);
    }


    /**
     * Log Fenom method call
     *
     * @param $method
     * @param $name
     * @param array $properties
     */
    public function method($method, $name, $properties = [])
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

        $this->log($tag);
    }


    /**
     * Pass data to debugParser
     *
     * @param $tag
     */
    public function log($tag)
    {
        $tag = preg_replace('#\s+#', ' ', $tag);
        $hash = sha1($tag);

        if (!isset($this->_tags[$hash])) {
            $this->_tags[$hash] = [
                'queries' => $this->modx->executedQueries,
                'queries_time' => $this->modx->queryTime,
                'parse_time' => microtime(true),
            ];
        } else {
            $queries = $this->modx->executedQueries - $this->_tags[$hash]['queries'];
            $queries_time = number_format(round($this->modx->queryTime - $this->_tags[$hash]['queries_time'], 7), 7);
            $parse_time = number_format(round(microtime(true) - $this->_tags[$hash]['parse_time'], 7), 7);
            if (!isset($parser->tags[$hash])) {
                $this->tags[$hash] = [
                    'tag' => $tag,
                    'attempts' => 1,
                    'queries' => $queries,
                    'queries_time' => $queries_time,
                    'parse_time' => $parse_time,
                ];
            } else {
                $this->tags[$hash]['attempts'] += 1;
                $this->tags[$hash]['queries'] += $queries;
                $this->tags[$hash]['queries_time'] += $queries_time;
                $this->tags[$hash]['parse_time'] += $parse_time;
            }
            unset($this->_tags[$hash]);
        }
    }


    /**
     * Generates table with report
     *
     * @return array
     */
    public function getReport()
    {
        // Total values
        $data = [
            'entries' => [],
            'total_queries' => $this->modx->executedQueries,
            'total_queries_time' => number_format(round($this->modx->queryTime, 7), 7),
            'total_parse_time' => number_format(round(microtime(true) - $this->modx->startTime, 7), 7),
        ];

        $time = [];
        // Sort tags by time
        foreach ($this->tags as $hash => $tag) {
            $time[$hash] = $tag['parse_time'];
        }
        arsort($time);

        $idx = 1;
        foreach ($time as $k => $v) {
            $row = $this->tags[$k];
            if (empty($row['tag'])) {
                continue;
            }
            if ($row['queries'] === 0) {
                $row['queries_time'] = 0;
            }
            $row['idx'] = $idx++;
            $data['entries'][] = $row;
        }

        /** @var ProcessorResponse $response */
        $response = $this->modx->runProcessor('system/info');
        if (!$response->isError()) {
            $data = array_merge($data, $response->response['object']);
        }

        $data['memory_peak'] = memory_get_peak_usage(true) / 1048576;
        $data['php_version'] = PHP_VERSION;
        $data['from_cache'] = $this->from_cache ? 'true' : 'false';

        return $data;
    }

}