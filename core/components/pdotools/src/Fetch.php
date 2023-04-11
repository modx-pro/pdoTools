<?php

namespace ModxPro\PdoTools;


use MODX\Revolution\modResource;
use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modTemplateVarResource;
use MODX\Revolution\modWebLink;
use MODX\Revolution\modX;
use PDO;
use PDOStatement;
use xPDO\Om\xPDOQuery;
use xPDO\xPDO;

class Fetch extends CoreTools
{
    /** @var string $pk Primary key of class */
    protected $pk;
    /** @var array $ancestry Array with ancestors of class */
    protected $ancestry = [];
    /** @var xPDOQuery $query */
    protected $query;
    /** @var array $aliases Array with aliases of classes */
    public $aliases;


    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config = [], $clean_timings = true)
    {
        $config += [
            'class' => modResource::class,
            'limit' => 10,
            'sortby' => '',
            'sortdir' => '',
            'groupby' => '',
            'totalVar' => 'total',
            'setTotal' => false,
            'tpl' => '',
            'return' => 'chunks',    // chunks, data, sql or ids

            'select' => '',
            'leftJoin' => '',
            'rightJoin' => '',
            'innerJoin' => '',

            'includeTVs' => '',
            'tvPrefix' => '',
            'tvsJoin' => [],
            'tvsSelect' => [],

            'tvFiltersAndDelimiter' => ',',
            'tvFiltersOrDelimiter' => '||',

            'additionalPlaceholders' => '',
            'useWeblinkUrl' => false,
        ];
        parent::setConfig($config, $clean_timings);

        if (empty($this->config['class'])) {
            $this->config['class'] = modResource::class;
        }
        $this->loadModels();
        $this->ancestry = $this->modx->getAncestry($this->config['class']);
        $pk = $this->modx->getPK($this->config['class']);
        $this->pk = is_array($pk)
            ? implode(',', $pk)
            : $pk;
        if (!isset($this->config['idx']) || !is_numeric($this->config['idx'])) {
            $this->config['idx'] = 1;
        }
        $this->idx = !empty($this->config['offset'])
            ? (int)$this->config['offset'] + $this->config['idx']
            : (int)$this->config['idx'];
    }


    /**
     * Main method for query processing and fetching rows
     * It can return string with SQL query, array or raw rows or processed HTML chunks
     *
     * @return array|bool|string
     */
    public function run()
    {
        $this->makeQuery();
        $this->addTVFilters();
        $this->addTVs();
        $this->addJoins();
        $this->addGrouping();
        $this->addSelects();
        $this->addWhere();
        $this->addSort();
        $this->prepareQuery();

        $output = '';
        if (strtolower($this->config['return']) == 'sql') {
            $this->addTime('Returning raw sql query');
            $output = $this->query->toSQL();
        } else {
            $this->modx->exec('SET SQL_BIG_SELECTS = 1');
            $this->addTime('SQL prepared <small>"' . $this->query->toSQL() . '"</small>');
            $tstart = microtime(true);
            if ($this->query->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                $this->addTime('SQL executed', microtime(true) - $tstart);
                $this->setTotal();

                $rows = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->addTime('Rows fetched');
                $rows = $this->checkPermissions($rows);
                $this->count = count($rows);
                if (strtolower($this->config['return']) === 'ids') {
                    $ids = [];
                    foreach ($rows as $row) {
                        $ids[] = $row[$this->pk];
                    }
                    $output = implode(',', $ids);
                } elseif (strtolower($this->config['return']) === 'data') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data');
                    $output = &$rows;
                } elseif (strtolower($this->config['return']) === 'json') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data as JSON string');
                    $output = json_encode($rows);
                } elseif (strtolower($this->config['return']) === 'serialize') {
                    $rows = $this->prepareRows($rows);
                    $this->addTime('Returning raw data as serialized string');
                    $output = serialize($rows);
                } else {
                    $rows = $this->prepareRows($rows);
                    $time = microtime(true);
                    $output = [];
                    foreach ($rows as $row) {
                        if (!empty($this->config['additionalPlaceholders'])) {
                            $row = array_merge($this->config['additionalPlaceholders'], $row);
                        }
                        $row['idx'] = $this->idx++;

                        // Add placeholder [[+link]] if specified
                        if (!empty($this->config['useWeblinkUrl'])) {
                            if (!isset($row['context_key'])) {
                                $row['context_key'] = '';
                            }
                            if (isset($row['class_key']) && ($row['class_key'] === modWebLink::class)) {
                                $row['link'] = isset($row['content']) && is_numeric(trim($row['content'], '[]~ '))
                                    ? $this->makeUrl((int)trim($row['content'], '[]~ '), $row)
                                    : (isset($row['content']) ? $row['content'] : '');
                            } else {
                                $row['link'] = $this->makeUrl($row['id'], $row);
                            }
                        } elseif (!isset($row['link'])) {
                            $row['link'] = '';
                        }

                        $tpl = $this->defineChunk($row);
                        if ($this->modx->getOption('pdotools_remove_user_sensitive_data', null, true)) {
                            $row = array_diff_key(
                                $row,
                                ['sessionid' => 1, 'password' => 1, 'cachepwd' => 1, 'salt' => 1, 'session_stale' => 1, 'remote_key' => 1, 'remote_data' => 1, 'hash_class' => 1]
                            );
                        }
                        if (empty($tpl)) {
                            $output[] = '<pre>' . $this->getChunk('', $row) . '</pre>';
                        } else {
                            $output[] = $this->getChunk($tpl, $row, $this->config['fastMode']);
                        }
                    }
                    $this->addTime('Returning processed chunks', microtime(true) - $time);

                    if (!empty($this->config['toSeparatePlaceholders'])) {
                        $this->modx->setPlaceholders($output, $this->config['toSeparatePlaceholders']);
                        $output = '';
                    } else {
                        $output = implode($this->config['outputSeparator'], $output);
                    }
                }
            } else {
                $this->modx->log(xPDO::LOG_LEVEL_INFO, '[pdoTools] ' . $this->query->toSQL());
                $errors = $this->query->stmt->errorInfo();
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[pdoTools] Error ' . $errors[0] . ': ' . $errors[2]);
                $this->addTime('Could not process query, error #' . $errors[1] . ': ' . $errors[2]);
            }
        }

        return $output;
    }


    /**
     * Create object with xPDOQuery
     */
    public function makeQuery()
    {
        $time = microtime(true);
        $this->query = $this->modx->newQuery($this->config['class']);
        $this->addTime('xPDO query object created', microtime(true) - $time);
    }


    /**
     * Adds where and having conditions
     */
    public function addWhere()
    {
        $time = microtime(true);
        $where = [];
        if (!empty($this->config['where'])) {
            $tmp = $this->config['where'];
            if (is_string($tmp) && ($tmp[0] === '{' || $tmp[0] === '[')) {
                $tmp = json_decode($tmp, true);
            }
            if (!is_array($tmp)) {
                $tmp = [$tmp];
            }
            $where = $this->replaceTVCondition($tmp);
        }
        $where = $this->additionalConditions($where);
        if (!empty($where)) {
            $this->query->where($where);
            $condition = [];
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[0])) {
                        $condition[] = is_array($v) ? $k . '(' . implode(',', $v) . ')' : $k . '=' . $v;
                    } else {
                        foreach ($v as $k2 => $v2) {
                            $condition[] = is_array($v2) ? $k2 . '(' . implode(',', $v2) . ')' : $k2 . '=' . $v2;
                        }
                    }
                } else {
                    $condition[] = $k . '=' . $v;
                }
            }
            $this->addTime('Added where condition: <b>' . implode(', ', $condition) . '</b>', microtime(true) - $time);
        }
        $time = microtime(true);
        if (!empty($this->config['having'])) {
            $tmp = $this->config['having'];
            if (is_string($tmp) && ($tmp[0] === '{' || $tmp[0] === '[')) {
                $tmp = json_decode($tmp, true);
            }
            if (!is_array($tmp)) {
                $tmp = [$tmp];
            }
            $having = $this->replaceTVCondition($tmp);
            $this->query->having($having);

            $condition = [];
            foreach ($having as $k => $v) {
                if (is_array($v)) {
                    $condition[] = $k . '(' . implode(',', $v) . ')';
                } else {
                    $condition[] = $k . '=' . $v;
                }
            }
            $this->addTime('Added having condition: <b>' . implode(', ', $condition) . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Set "total" placeholder for pagination
     */
    public function setTotal()
    {
        if ($this->config['setTotal'] && !in_array($this->config['return'], ['sql', 'ids'])) {
            $time = microtime(true);

            $q = $this->modx->prepare("SELECT FOUND_ROWS();");

            $tstart = microtime(true);
            $q->execute();
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;

            $total = $q->fetch(PDO::FETCH_COLUMN);
            $this->modx->setPlaceholder($this->config['totalVar'], $total);

            $this->addTime('Total rows: <b>' . $total . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Add tables join to query
     */
    public function addJoins()
    {
        $time = microtime(true);
        // left join is always needed because of TVs
        if (empty($this->config['leftJoin'])) {
            $this->config['leftJoin'] = '[]';
        }

        $joinSequence = ['innerJoin', 'leftJoin', 'rightJoin'];
        if (!empty($this->config['joinSequence'])) {
            if (is_string($this->config['joinSequence'])) {
                $this->config['joinSequence'] = array_map('trim', explode(',', $this->config['joinSequence']));
            }
            if (is_array($this->config['joinSequence'])) {
                $joinSequence = $this->config['joinSequence'];
            }
        }

        foreach ($joinSequence as $join) {
            if (!empty($this->config[$join])) {
                $tmp = $this->config[$join];
                if (is_string($tmp) && ($tmp[0] === '{' || $tmp[0] === '[')) {
                    $tmp = json_decode($tmp, true);
                }
                if ($join == 'leftJoin' && !empty($this->config['tvsJoin'])) {
                    $tmp = array_merge($tmp, $this->config['tvsJoin']);
                }
                foreach ($tmp as $k => $v) {
                    $class = !empty($v['class']) ? $v['class'] : $k;
                    $alias = $this->modx->getAlias(!empty($v['alias']) ? $v['alias'] : $k);
                    $on = !empty($v['on']) ? $v['on'] : [];
                    if (!is_numeric($alias) && !is_numeric($class)) {
                        $this->query->$join($class, $alias, $on);
                        $this->addTime($join . 'ed <i>' . $class . '</i> as <b>' . $alias . '</b>',
                            microtime(true) - $time);
                        $this->aliases[$alias] = $class;
                    } else {
                        $this->addTime('Could not ' . $join . ' <i>' . $class . '</i> as <b>' . $alias . '</b>',
                            microtime(true) - $time);
                    }
                    $time = microtime(true);
                }
            }
        }
    }


    /**
     * Add select of fields
     */
    public function addSelects()
    {
        $time = microtime(true);

        $alias = $this->modx->getAlias($this->config['class']);

        if ($this->config['return'] == 'ids') {
            $this->query->select("`{$alias}`.`{$this->pk}`");
            $this->addTime('Parameter "return" set to "ids", so we select only primary key', microtime(true) - $time);
        } elseif ($tmp = $this->config['select']) {
            if (!is_array($tmp)) {
                $tmp = (!empty($tmp) && ($tmp[0] === '{' || $tmp[0] === '['))
                    ? json_decode($tmp, true)
                    : [$alias => $tmp];
            }
            if (!is_array($tmp)) {
                $tmp = [];
            }
            $tmp = array_merge($tmp, $this->config['tvsSelect']);
            $i = 0;
            foreach ($tmp as $alias => $fields) {
                if (is_numeric($alias)) {
                    $class = $this->config['class'];
                    $alias = $this->modx->getAlias($class);
                } elseif (isset($this->aliases[$alias])) {
                    $class = $this->aliases[$alias];
                } else {
                    $class = class_exists('MODX\Revolution\\' . $alias)
                        ? 'MODX\Revolution\\' . $alias
                        : $alias;
                    $alias = $this->modx->getAlias($class);
                }

                if (is_string($fields) && !preg_match('/\b' . $alias . '\b|\bAS\b|\(|`/i', $fields)) {
                    if ($fields == 'all' || $fields == '*' || empty($fields)) {
                        $fields = $this->modx->getSelectColumns($class, $alias);
                    } else {
                        $fields = $this->modx->getSelectColumns($class, $alias, '', array_map('trim', explode(',', $fields)));
                    }
                }

                if ($i == 0 && $this->config['setTotal']) {
                    $fields = 'SQL_CALC_FOUND_ROWS ' . $fields;
                }

                if (is_string($fields) && strpos($fields, '(') !== false) {
                    // Commas in functions
                    $fields = preg_replace_callback('/\(.*?\)/', function ($matches) {
                        return str_replace(",", "|", $matches[0]);
                    }, $fields);
                    $fields = explode(',', $fields);
                    foreach ($fields as &$field) {
                        $field = str_replace('|', ',', $field);
                    }
                    $this->query->select($fields);
                    $this->addTime('Added selection of <b>' . $alias . '</b>: <small>' . str_replace('`' . $alias . '`.', '', implode(',', $fields)) . '</small>', microtime(true) - $time);
                } else {
                    $this->query->select($fields);
                    if (is_array($fields)) {
                        $fields = current($fields) . ' AS ' . current(array_flip($fields));
                    }
                    $this->addTime('Added selection of <b>' . $alias . '</b>: <small>' . str_replace('`' . $alias . '`.', '', $fields) . '</small>', microtime(true) - $time);
                }

                $i++;
                $time = microtime(true);
            }
        } else {
            $columns = array_keys($this->modx->getFieldMeta($this->config['class']));
            if (isset($this->config['includeContent']) && empty($this->config['includeContent']) && empty($this->config['useWeblinkUrl'])) {
                $key = array_search('content', $columns);
                if ($key !== false) {
                    unset($columns[$key]);
                }
            }
            $this->config['select'] = [$this->config['class'] => implode(',', $columns)];
            $this->addSelects();
        }
    }


    /**
     * Group query by given field
     */
    public function addGrouping()
    {
        if (!empty($this->config['groupby'])) {
            $time = microtime(true);
            $groupby = $this->config['groupby'];
            $this->query->groupby($groupby);
            $this->addTime('Grouped by <b>' . $groupby . '</b>', microtime(true) - $time);
        }
    }


    /**
     * Add sort to query
     */
    public function addSort()
    {
        $time = microtime(true);
        $alias = $this->modx->getAlias($this->config['class']);
        $tmp = $this->config['sortby'];
        if (empty($tmp) || (is_string($tmp) && in_array(strtolower($tmp), ['resources', 'ids']))) {
            $resources = $alias . '.' . $this->pk . ':IN';
            if (!empty($this->config['where'][$resources])) {
                $tmp = [
                    'FIELD(`' . $alias . '`.`' . $this->pk . '`,\'' . implode('\',\'',
                        $this->config['where'][$resources]) . '\')' => '',
                ];
            } else {
                $tmp = [
                    $alias . '.' . $this->pk => !empty($this->config['sortdir'])
                        ? $this->config['sortdir']
                        : 'ASC',
                ];
            }
        } elseif (is_string($tmp)) {
            $tmp = $tmp[0] === '{' || $tmp[0] === '['
                ? json_decode($tmp, true)
                : [$tmp => $this->config['sortdir']];
        }
        if (!empty($this->config['sortbyTV']) && !array_key_exists($this->config['sortbyTV'], $tmp)) {
            $tmp2[$this->config['sortbyTV']] = !empty($this->config['sortdirTV'])
                ? $this->config['sortdirTV']
                : (!empty($this->config['sortdir'])
                    ? $this->config['sortdir']
                    : 'ASC'
                );
            $tmp = array_merge($tmp2, $tmp);
            if (!empty($this->config['sortbyTVType'])) {
                $tv = strtolower($this->config['sortbyTV']);
                if (array_key_exists($tv, $this->config['tvsJoin'])) {
                    if (!empty($this->config['tvsJoin'][$tv]['tv'])) {
                        $this->config['tvsJoin'][$tv]['tv']['type'] = $this->config['sortbyTVType'];
                    }
                }
            }
        }

        $fields = $this->modx->getFields($this->config['class']);
        $sorts = $this->replaceTVCondition($tmp);
        foreach ($sorts as $sortby => $sortdir) {
            if (preg_match_all('#`TV(.*?)`#', $sortby, $matches)) {
                foreach ($matches[1] as $tv) {
                    if (array_key_exists($tv, $this->config['tvsJoin'])) {
                        $params = $this->config['tvsJoin'][$tv]['tv'];
                        switch ($params['type']) {
                            case 'number':
                            case 'decimal':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS DECIMAL(13,3))', $sortby);
                                break;
                            case 'int':
                            case 'integer':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS SIGNED INTEGER)', $sortby);
                                break;
                            case 'date':
                            case 'datetime':
                                $sortby = preg_replace('#(TV' . $tv . '\.value|`TV' . $tv . '`\.`value`)#',
                                    'CAST($1 AS DATETIME)', $sortby);
                                break;
                        }
                    }
                }
            } elseif (array_key_exists($sortby, $fields)) {
                $sortby = $alias . '.' . $sortby;
            }
            // Escaping of columns names
            $tmp = explode(',', $sortby);
            array_walk($tmp, function (&$value) {
                if (strpos($value, '`') === false) {
                    $value = preg_replace('#(.*?)\.(.*?)\s#', '`$1`.`$2`', $value);
                }
            });
            $sortby = implode(',', $tmp);
            if (!in_array(strtoupper($sortdir), ['ASC', 'DESC', ''], true)) {
                $sortdir = 'ASC';
            }

            if (!xPDOQuery::isValidClause($sortby)) {
                $message = 'SQL injection attempt detected in sortby column; clause rejected';
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message);
                $this->addTime($message . ': ' . $sortby);
            } elseif (!empty($sortby)) {
                $this->query->query['sortby'][] = [
                    'column' => $sortby,
                    'direction' => $sortdir,
                ];
                $this->addTime('Sorted by <b>' . $sortby . '</b>, <b>' . $sortdir . '</b>', microtime(true) - $time);
            }
        }
    }


    /**
     * Set parameters and prepare query
     *
     * @return PDOStatement
     */
    public function prepareQuery()
    {
        if ($limit = (int)$this->config['limit']) {
            $offset = (int)$this->config['offset'];
            $time = microtime(true);
            $this->query->limit($limit, $offset);
            $this->addTime('Limited to <b>' . $limit . '</b>, offset <b>' . $offset . '</b>', microtime(true) - $time);
        }

        return $this->query->prepare();
    }


    /**
     * Add selection of template variables to query
     */
    public function addTVs()
    {
        $time = microtime(true);

        $includeTVs = $this->config['includeTVs'];
        $tvPrefix = !empty($this->config['tvPrefix']) ?
            trim($this->config['tvPrefix'])
            : '';

        if (!empty($this->config['includeTVList']) && (empty($includeTVs) || is_numeric($includeTVs))) {
            $this->config['includeTVs'] = $includeTVs = $this->config['includeTVList'];
        }
        if (!empty($this->config['sortbyTV'])) {
            $includeTVs .= empty($includeTVs)
                ? $this->config['sortbyTV']
                : ',' . $this->config['sortbyTV'];
        }

        if (!empty($includeTVs)) {
            $class = !empty($this->config['joinTVsTo'])
                ? $this->config['joinTVsTo']
                : $this->config['class'];
            $alias = $this->modx->getAlias($class);
            if ($alias !== 'modResource' && !in_array($class, $this->modx->classMap[modResource::class])) {
                $this->modx->log(
                    xPDO::LOG_LEVEL_ERROR,
                    '[pdoTools] Could not join TVs to the class "' . $class . '" that is not a subclass of the "modResource". Try to specify correct class in the "joinTVsTo" parameter.');

                    return;
            }

            $tvs = array_map('trim', explode(',', $includeTVs));
            $tvs = array_unique($tvs);
            if (!empty($tvs)) {
                $q = $this->modx->newQuery(modTemplateVar::class, ['name:IN' => $tvs]);
                $q->select('id,name,type,default_text');
                $tstart = microtime(true);
                if ($q->prepare() && $q->stmt->execute()) {
                    $this->modx->queryTime += microtime(true) - $tstart;
                    $this->modx->executedQueries++;
                    $tvs = [];
                    while ($tv = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                        $name = strtolower($tv['name']);
                        $alias_tv = 'TV' . $name;
                        $this->config['tvsJoin'][$name] = [
                            'class' => modTemplateVarResource::class,
                            'alias' => $alias_tv,
                            'on' => "`{$alias_tv}`.`contentid` = `{$alias}`.`id` AND `{$alias_tv}`.`tmplvarid` = {$tv['id']}",
                            'tv' => $tv,
                        ];
                        $this->config['tvsSelect'][$alias_tv] = ["`{$tvPrefix}{$tv['name']}`" => "IFNULL(`{$alias_tv}`.`value`, {$this->modx->quote($tv['default_text'])})"];
                        $tvs[] = $tv['name'];
                    }

                    $this->addTime('Included list of tvs: <b>' . implode(', ', $tvs) . '</b>', microtime(true) - $time);
                }
            }
        }
    }


    /**
     * This method handles popular parameters and adds conditions to query
     *
     * @param array $where Current conditions
     *
     * @return array
     */
    public function additionalConditions($where = [])
    {
        $config = $this->config;
        $class = $this->config['class'];
        $alias = $this->modx->getAlias($this->config['class']);

        // These rules works only for descendants of modResource
        if (!in_array(modResource::class, $this->ancestry) || !empty($config['disableConditions'])) {
            return $where;
        }
        $time = microtime(true);

        $params = [
            'resources' => 'id',
            'parents' => 'parent',
            'templates' => 'template',
            'showUnpublished' => 'published',
            'showHidden' => 'hidemenu',
            'showDeleted' => 'deleted',
            'hideContainers' => 'isfolder',
            'hideUnsearchable' => 'searchable',
            'context' => 'context_key',
        ];

        // Exclude parameters that may already have been processed
        foreach ($params as $param => $field) {
            $found = false;
            if (isset($config[$param])) {
                foreach ($where as $k => $v) {
                    // Usual condition
                    if (!is_numeric($k) && strpos($k, $field) === 0 || strpos($k, $alias . '.' . $field) !== false) {
                        $found = true;
                        break;
                    } // Array of conditions
                    elseif (is_numeric($k) && is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if (strpos($k2, $field) === 0 || strpos($k2, $alias . '.' . $field) !== false) {
                                $found = true;
                                break(2);
                            }
                        }
                    } // Raw SQL string
                    elseif (is_numeric($k) && strpos($v, $alias) !== false && preg_match('/\b' . $field . '\b/i', $v)) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    unset($params[$param]);
                } else {
                    $params[$param] = $config[$param];
                }
            } else {
                unset($params[$param]);
            }
        }

        // Process the remaining parameters
        foreach ($params as $param => $value) {
            switch ($param) {
                case 'showUnpublished':
                    if (empty($value)) {
                        $where[$alias . '.published'] = 1;
                    }
                    break;
                case 'showHidden':
                    if (empty($value)) {
                        $where[$alias . '.hidemenu'] = 0;
                    }
                    break;
                case 'showDeleted':
                    if (empty($value)) {
                        $where[$alias . '.deleted'] = 0;
                    }
                    break;
                case 'hideContainers':
                    if (!empty($value)) {
                        $where[$alias . '.isfolder'] = 0;
                    }
                    break;
                case 'hideUnsearchable':
                    if (!empty($value)) {
                        $where[$alias . '.searchable'] = 1;
                    }
                    break;
                case 'context':
                    if (!empty($value)) {
                        $context = array_map('trim', explode(',', $value));
                        if (!empty($context) && is_array($context)) {
                            if (count($context) === 1) {
                                $where[$alias . '.context_key'] = $context[0];
                            } else {
                                $where[$alias . '.context_key:IN'] = $context;
                            }
                        }
                    }
                    break;
                case 'resources':
                    if (!empty($value)) {
                        $resources = array_map('trim', explode(',', $value));
                        $resources_in = $resources_out = [];
                        foreach ($resources as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v < 0) {
                                $resources_out[] = abs($v);
                            } else {
                                $resources_in[] = abs($v);
                            }
                        }
                        if (!empty($resources_in)) {
                            $where[$alias . '.id:IN'] = $resources_in;
                        }
                        if (!empty($resources_out)) {
                            $where[$alias . '.id:NOT IN'] = $resources_out;
                        }
                    }
                    break;
                case 'parents':
                    if (!empty($value)) {
                        $parents = array_map('trim', explode(',', $value));
                        $parents_in = $parents_out = [];
                        foreach ($parents as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v[0] === '-') {
                                $parents_out[] = abs($v);
                            } else {
                                $parents_in[] = abs($v);
                            }
                        }
                        $depth = (isset($config['depth']) && $config['depth'] !== '')
                            ? (int)$config['depth']
                            : 10;
                        if (!empty($depth) && $depth > 0) {
                            $pids = [];
                            $q = $this->modx->newQuery($class, ['id:IN' => array_merge($parents_in, $parents_out)]);
                            $q->select('id,context_key');
                            $tstart = microtime(true);
                            if ($q->prepare() && $q->stmt->execute()) {
                                $this->modx->queryTime += microtime(true) - $tstart;
                                $this->modx->executedQueries++;
                                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $pids[$row['id']] = $row['context_key'];
                                }
                            }
                            foreach ($pids as $k => $v) {
                                if (!is_numeric($k)) {
                                    continue;
                                } elseif (in_array($k, $parents_in)) {
                                    $parents_in = array_merge($parents_in,
                                        $this->modx->getChildIds($k, $depth, ['context' => $v]));
                                } else {
                                    $parents_out = array_merge($parents_out,
                                        $this->modx->getChildIds($k, $depth, ['context' => $v]));
                                }
                            }
                            if (empty($parents_in)) {
                                $parents_in = $this->modx->getChildIds(0, $depth, ['context' => $this->config['context']]);
                            }
                        }
                        // Support of miniShop2 categories
                        $members = [];
                        if (in_array('msCategory', $this->modx->classMap[modResource::class]) && empty($this->config['disableMS2'])) {
                            if (!empty($parents_in) || !empty($parents_out)) {
                                $q = $this->modx->newQuery('msCategoryMember');
                                if (!empty($parents_in)) {
                                    $q->where(['category_id:IN' => $parents_in]);
                                }
                                if (!empty($parents_out)) {
                                    $q->where(['category_id:NOT IN' => $parents_out]);
                                }
                                $q->select('product_id');
                                $tstart = microtime(true);
                                if ($q->prepare() && $q->stmt->execute()) {
                                    $this->modx->queryTime += microtime(true) - $tstart;
                                    $this->modx->executedQueries++;
                                    $members = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
                                }
                            }
                        }
                        // Add parent to conditions
                        if (!empty($parents_in) && !empty($members)) {
                            if (!empty($this->config['includeParents'])) {
                                $members = array_merge($members, $parents_in);
                            }
                            $where[] = [
                                $alias . '.parent:IN' => $parents_in,
                                'OR:' . $alias . '.id:IN' => $members,
                            ];
                        } elseif (!empty($parents_in) && !empty($this->config['includeParents'])) {
                            $where[] = [
                                $alias . '.parent:IN' => $parents_in,
                                'OR:' . $alias . '.id:IN' => $parents_in,
                            ];
                        } elseif (!empty($parents_in)) {
                            $where[$alias . '.parent:IN'] = $parents_in;
                        }
                        if (!empty($parents_out) && !empty($this->config['includeParents'])) {
                            $where[] = [
                                $alias . '.parent:NOT IN' => $parents_out,
                                'AND:' . $alias . '.id:NOT IN' => $parents_out,
                            ];
                        } elseif (!empty($parents_out)) {
                            $where[$alias . '.parent:NOT IN'] = $parents_out;
                        }
                    }
                    break;
                case 'templates':
                    if (!empty($value)) {
                        $templates = array_map('trim', explode(',', $value));
                        $templates_in = $templates_out = [];
                        foreach ($templates as $v) {
                            if (!is_numeric($v)) {
                                continue;
                            }
                            if ($v[0] == '-') {
                                $templates_out[] = abs($v);
                            } else {
                                $templates_in[] = abs($v);
                            }
                        }
                        if (!empty($templates_in)) {
                            $where[$alias . '.template:IN'] = $templates_in;
                        }
                        if (!empty($templates_out)) {
                            $where[$alias . '.template:NOT IN'] = $templates_out;
                        }
                    }
                    break;
            }
        }
        $this->config['where'] = $where;

        $this->addTime('Processed additional conditions', microtime(true) - $time);

        return $where;
    }


    /**
     * Convert tvFilters string to SQL and add to "where" condition
     * This algorithm taken from snippet getResources by opengeek
     */
    public function addTVFilters()
    {
        $time = microtime(true);

        if (empty($this->config['tvFilters'])) {
            return;
        }
        $tvFiltersAndDelimiter = $this->config['tvFiltersAndDelimiter'];
        $tvFiltersOrDelimiter = $this->config['tvFiltersOrDelimiter'];

        $tvFilters = array_map('trim', explode($tvFiltersOrDelimiter, $this->config['tvFilters']));
        $operators = [
            '<=>' => '<=>',
            '===' => '=',
            '!==' => '!=',
            '<>' => '<>',
            '==' => 'LIKE',
            '!=' => 'NOT LIKE',
            '<<' => '<',
            '<=' => '<=',
            '=<' => '=<',
            '>>' => '>',
            '>=' => '>=',
            '=>' => '=>',
        ];

        $includeTVs = !empty($this->config['includeTVs'])
            ? array_map('trim', explode(',', $this->config['includeTVs']))
            : [];
        $where = [];
        if (!empty($this->config['where'])) {
            $tmp = $this->config['where'];
            if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
                $where = json_decode($tmp, true);
            }
            if (!is_array($where)) {
                $where = [$where];
            }
        }

        $conditions = [];
        foreach ($tvFilters as $tvFilter) {
            $condition = [];
            $filters = explode($tvFiltersAndDelimiter, $tvFilter);
            foreach ($filters as $filter) {
                $operator = '==';
                $sqlOperator = 'LIKE';
                foreach ($operators as $op => $opSymbol) {
                    if (strpos($filter, $op, 1) !== false) {
                        $operator = $op;
                        $sqlOperator = $opSymbol;
                        break;
                    }
                }
                $filter = array_map('trim', explode($operator, $filter));
                if (!in_array($filter[0], $includeTVs)) {
                    $includeTVs[] = $filter[0];
                }
                if (!is_numeric($filter[1])) {
                    $condition[] = $filter[0] . ' ' . $sqlOperator . ' ' . $this->modx->quote($filter[1]);
                } else {
                    $condition[] = $filter[0] . ' ' . $sqlOperator . ' ' . $filter[1];
                }
            }
            $conditions[] = implode(' AND ', $condition);
        }
        if (count($conditions) > 1) {
            $where[] = '((' . implode(') OR (', $conditions) . '))';
        } else {
            $where[] = $conditions[0];
        }

        $this->config['includeTVs'] = implode(',', $includeTVs);
        $this->config['where'] = $where;
        $this->addTime('Added TVs filters', microtime(true) - $time);
    }


    /**
     * Replaces tv fields to full name format.
     * For example, field "test" will be replaced with "TVtest.value", if template variable "test" was joined in query.
     *
     * @param array $array Array for replacement
     *
     * @return array $sorts Array with replaced conditions
     */
    public function replaceTVCondition(array $array)
    {
        if (empty($this->config['tvsJoin'])) {
            return $array;
        }

        $time = microtime(true);
        $tvs = implode('|', array_keys($this->config['tvsJoin']));

        $sorts = [];
        foreach ($array as $k => $v) {
            $callback = function ($matches) {
                return '`TV' . strtolower($matches[1]) . '`.`value`';
            };
            if (is_numeric($k) && is_string($v)) {
                $tmp = preg_replace_callback('/\b(' . $tvs . ')\b/i', $callback, $v);
                $sorts[$k] = $tmp;
            } elseif (is_numeric($k) && is_array($v)) {
                $sorts[$k] = $this->replaceTVCondition($v);
            } else {
                $tmp = preg_replace_callback('/\b(' . $tvs . ')\b/i', $callback, $k);
                $sorts[$tmp] = $v;
            }
        }
        $this->addTime('Replaced TV conditions', microtime(true) - $time);

        return $sorts;
    }


    /**
     * Alias for getArray method
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return array
     */
    public function getObject($class, $where = '', $config = [])
    {
        return $this->getArray($class, $where, $config);
    }


    /**
     * PDO replacement for modX::getObject()
     * Returns array instead of object
     *
     * @param $class
     * @param string $where
     * @param array $config
     *
     * @return array
     */
    public function getArray($class, $where = '', $config = [])
    {
        $config['limit'] = 1;
        $rows = $this->getCollection($class, $where, $config);

        return !empty($rows[0])
            ? $rows[0]
            : [];
    }


    /**
     * PDO replacement for modX::getCollection()
     *
     * @param string $class
     * @param string|int|array $where
     * @param array $config
     *
     * @return array|boolean
     */
    public function getCollection($class, $where = '', array $config = [])
    {
        $instance = new self($this->modx, $config, $this->fenom);

        $config['class'] = $class;
        $config['limit'] = !isset($config['limit'])
            ? 0
            : (int)$config['limit'];
        if (!empty($where)) {
            unset($config['where']);
            if (is_numeric($where)) {
                $where = [$instance->modx->getPK($class) => (int)$where];
            } elseif (is_string($where) && ($where[0] === '{' || $where[0] === '[')) {
                $where = json_decode($where, true);
            }
            if (is_array($where)) {
                $config['where'] = $where;
            }
        }

        $instance->setConfig($config, true);
        $instance->makeQuery();
        $instance->addTVFilters();
        $instance->addTVs();
        $instance->addJoins();
        $instance->addGrouping();
        $instance->addSelects();
        $instance->addWhere();
        $instance->addSort();
        $instance->prepareQuery();
        $instance->modx->exec('SET SQL_BIG_SELECTS = 1');
        $instance->addTime('SQL prepared <small>"' . $instance->query->toSQL() . '"</small>');

        $rows = '';
        $tstart = microtime(true);
        if ($instance->query->stmt->execute()) {
            $instance->addTime('SQL executed', microtime(true) - $tstart);
            $instance->modx->queryTime += microtime(true) - $tstart;
            $instance->modx->executedQueries++;
            $tstart = microtime(true);
            if (!$rows = $instance->query->stmt->fetchAll(PDO::FETCH_ASSOC)) {
                $rows = [];
            } else {
                $rows = $instance->checkPermissions($rows);
                $rows = $instance->prepareRows($rows);
            }
            $instance->addTime('Total rows: ' . count($rows));
            $instance->addTime('Rows are fetched', microtime(true) - $tstart);
        } else {
            $errors = $instance->query->stmt->errorInfo();
            $instance->modx->log(modX::LOG_LEVEL_ERROR,
                '[pdoTools] Could not load collection of "' . $class . '": Error ' . $errors[0] . ': ' . $errors[2]);
            $instance->addTime('Could not process query, error #' . $errors[1] . ': ' . $errors[2]);
        }

        $this->modx->setPlaceholder('pdoTools.log', $instance->getTime());

        return $rows;
    }


    /**
     * Gets all of the child objects ids for a given object.
     *
     * @param string $class Object class
     * @param integer $id The object parent id for the starting node.
     * @param integer $depth How many levels max to search for children (default 10).
     * @param array $options An array of options, such as 'where','parent_field','depth', 'sortby' etc.
     *
     * @return array
     */
    public function getChildIds($class, $id, $depth = 10, array $options = [])
    {
        $ids = [];
        $where = isset($options['where']) && is_array($options['where'])
            ? $options['where']
            : [];
        $id_field = !empty($options['id_field'])
            ? $options['id_field']
            : 'id';
        $parent_field = !empty($options['parent_field'])
            ? $options['parent_field']
            : 'parent';
        if (empty($options['select'])) {
            $options['select'] = $id_field;
        }
        $options['return'] = 'ids';

        if ($id !== null && (int)$depth >= 1) {
            $where[$parent_field] = (int)$id;
            $children = $this->getCollection($class, $where, $options);
            foreach ($children as $child) {
                $ids[] = $child[$id_field];
                if ($tmp = $this->getChildIds($class, $child[$id_field], $depth - 1, $options)) {
                    $ids = array_merge($ids, $tmp);
                }
            }
        }

        return $ids;
    }
}
