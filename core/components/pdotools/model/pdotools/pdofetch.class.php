<?php
require_once 'pdotools.class.php';

class pdoFetch extends pdoTools {
	/* @var xPDOQuery $query */
	protected $query;


	public function __construct(modX $modx, array $config = array()) {
		parent::__construct($modx);

		$this->setConfig($config);
	}


	/**
	 * Set default query options and merge it with given config.
	 * Need for multiple instances of pdoFetch snippets at the one page.
	 *
	 */
	public function setConfig(array $config = array(), $clean_timings = true) {
		$this->config = array_merge(array(
			'class' => 'modResource'
			,'limit' => 10
			,'offset' => 0
			,'sortby' => ''
			,'sortdir' => 'DESC'
			,'groupby' => ''
			,'totalVar' => 'total'
			,'outputSeparator' => "\n"
			,'tpl' => ''
			,'fastMode' => false
			,'return' => 'chunks'	 // chunks, data, sql or ids

			,'select' => ''
			,'leftJoin' => ''
			,'rightJoin' => ''
			,'innerJoin' => ''

			,'includeTVs' => ''
			,'tvPrefix' => ''
			,'tvsJoin' => array()
			,'tvsSelect' => array()

			,'tvFiltersAndDelimiter' => ','
			,'tvFiltersOrDelimiter' => '||'

			,'nestedChunkPrefix' => 'pdotools_'
			,'loadModels' => ''
		), $config);

		if (empty($this->config['sortby'])) {
			$this->config['sortby'] = $this->config['class'].'.'.$this->modx->getPK($this->config['class']);
		}
		$this->idx = !empty($this->config['offset']) ? (integer) $this->config['offset'] + 1 : 1;
		if ($clean_timings) {
			$this->timings = array();
		}
	}


	/**
	 * Main method for query processing
	 *
	 * @return array|bool|string
	 */
	public function run() {
		$this->loadModels();
		$this->makeQuery();
		$this->addTVs();
		$this->addJoins();
		$this->addGrouping();
		$this->addSelects();
		$this->addWhere();

		if (!$this->prepareQuery()) {return false;}

		$output = '';
		if (strtolower($this->config['return']) == 'sql') {
			$this->addTime('Returning raw sql query');
			$output = $this->query->toSql();
		}
		else {
			$this->addTime('SQL prepared <small>"'.$this->query->toSql().'"</small>');
			if ($this->query->stmt->execute()) {
				$this->addTime('SQL executed');

				$this->setTotal();

				$rows = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
				$this->addTime('Rows fetched');
				$this->count = count($rows);

				if (strtolower($this->config['return']) == 'ids') {
					$ids = array();
					foreach ($rows as $row) {
						$ids[] = $row['id'];
					}
					$output = implode(',', $ids);
				}
				elseif (strtolower($this->config['return']) == 'data') {
					$rows = $this->prepareResults($rows);
					$this->addTime('Returning raw data');
					$output = & $rows;
				}
				else {
					$rows = $this->prepareResults($rows);
					foreach ($rows as $v) {
						$v['idx'] = $this->idx++;
						$tpl = $this->defineChunk($v);
						if (empty($tpl)) {
							$output[] = '<pre>'.$this->getChunk('', $v).'</pre>';
						}
						else {
							$output[] = $this->getChunk($tpl, $v, $this->config['fastMode']);
						}
					}
					$this->addTime('Returning processed chunks');

					if (!empty($this->config['toSeparatePlaceholders'])) {
						$this->modx->setPlaceholders($output, $this->config['toSeparatePlaceholders']);
						$output = '';
					}
					elseif (!empty($output)) {
						$output = implode($this->config['outputSeparator'], $output);
					}
				}
			}
			else {
				$this->modx->log(modX::LOG_LEVEL_INFO, '[pdoTools] '.$this->query->toSql());
				$errors = $this->query->stmt->errorInfo();
				$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Error '.$errors[0].': '.$errors[2]);
			}
		}
		return $output;
	}


	/**
	 * Loads specified list of packages models
	 */
	public function loadModels() {
		if (empty($this->config['loadModels'])) {return;}

		$models = array();
		if (strpos(ltrim($this->config['loadModels']), '{') === 0) {
			$tmp = $this->modx->fromJSON($this->config['loadModels']);
			foreach ($tmp as $k => $v) {
				$v = trim(strtolower($v));
				$models[$k] = (strpos($v, MODX_CORE_PATH) === false)
					? MODX_CORE_PATH . ltrim($v, '/')
					: $v;
			}
		}
		else {
			$tmp = array_map('trim', explode(',', $this->config['loadModels']));
			foreach ($tmp as $v) {
				$models[$v] = MODX_CORE_PATH . 'components/'.strtolower($v).'/model/';
			}
		}

		if (!empty($models)) {
			foreach ($models as $k => $v) {
				$t = '/' . str_replace(MODX_BASE_PATH, '', $v);
				if ($this->modx->addPackage($k, $v)) {
					$this->addTime('Loaded model "'.$k.'" from "'.$t.'"');
				}
				else {
					$this->addTime('Could not load model "'.$k.'" from "'.$t.'"');
				}
			}
		}
	}

	/**
	 * Create object with xPDOQuery
	 *
	 */
	public function makeQuery() {
		$this->query = $this->modx->newQuery($this->config['class']);
		$this->addTime('xPDO query object created');
	}


	/**
	 * Adds where and having conditions
	 *
	 */
	public function addWhere() {
		$this->addTVFilters();
		if (!empty($this->config['where'])) {
			$where = $this->modx->fromJson($this->config['where']);
			$where = $this->replaceTVCondition($where);
			$this->query->where($where);

			$condition = array();
			foreach ($where as $k => $v) {
				if (is_array($v)) {$condition[] = $k.'('.implode(',',$v).')';}
				else {$condition[] = $k.'='.$v;}
			}
			$this->addTime('Added where condition: <b>' .implode(', ',$condition).'</b>');
		}
		if (!empty($this->config['having'])) {
			$having = $this->modx->fromJson($this->config['having']);
			$having = $this->replaceTVCondition($having);
			$this->query->having($having);

			$condition = array();
			foreach ($having as $k => $v) {
				if (is_array($v)) {$condition[] = $k.'('.implode(',',$v).')';}
				else {$condition[] = $k.'='.$v;}
			}
			$this->addTime('Added having condition: <b>' .implode(', ',$condition).'</b>');
		}
	}


	/**
	 * Set "total" placeholder for pagination
	 *
	 */
	public function setTotal() {
		if ($this->config['return'] != 'sql') {
			$q = $this->modx->prepare("SELECT FOUND_ROWS();");
			$q->execute();
			$total = $q->fetch(PDO::FETCH_COLUMN);
			$this->addTime('Total rows: <b>'.$total.'</b>');
			$this->modx->setPlaceholder($this->config['totalVar'], $total);
		}
	}


	/**
	 * Add tables join to query
	 *
	 */
	public function addJoins() {
		// left join is always need because of TVs
		if (empty($this->config['leftJoin'])) {
			$this->config['leftJoin'] = '[]';
		}

		foreach (array('innerJoin','leftJoin','rightJoin') as $join) {
			if (!empty($this->config[$join])) {
				$tmp = $this->modx->fromJSON($this->config[$join]);
				if ($join == 'leftJoin' && !empty($this->config['tvsJoin'])) {
					$tmp = array_merge($tmp, $this->config['tvsJoin']);
				}
				foreach ($tmp as $k => $v) {
					$class = !empty($v['class']) ? $v['class'] : $k;
					$this->query->$join($class, $v['alias'], $v['on']);
					$this->addTime($join.'ed <i>'.$class.'</i> as <b>'.$v['alias'].'</b>');
				}
			}
		}
	}


	/**
	 * Add select of fields
	 *
	 */
	public function addSelects() {
		if ($this->config['return'] == 'ids') {
			$this->query->select('
				SQL_CALC_FOUND_ROWS `'.$this->config['class'].'`.`id`
			');
			$this->addTime('Parameter "return" set to "ids", so we select only resource id');
		}
		elseif (!empty($this->config['select'])) {
			$tmp = array_merge($this->modx->fromJSON($this->config['select']), $this->config['tvsSelect']);
			$i = 0;
			foreach ($tmp as $k => $v) {
				if (is_numeric($k)) {$k = $this->config['class'];}
				if (strpos($k, 'TV') !== 0 && strpos($v, $k) === false && isset($this->modx->map[$k])) {
					if ($v == 'all' || $v == '*') {
						$v = $this->modx->getSelectColumns($k, $k);
					}
					else {
						$v = $this->modx->getSelectColumns($k, $k, '', array_map('trim', explode(',', $v)));
					}
				}
				if ($i == 0) {$v = 'SQL_CALC_FOUND_ROWS '.$v;}
				$this->query->select($v);
				$this->addTime('Added selection of <b>'.$k.'</b>: <small>' . str_replace('`'.$k.'`.', '', $v) . '</small>');
				$i++;
			}
		}
		else {
			$class = $this->config['class'];
			$select = 'SQL_CALC_FOUND_ROWS ' . $this->modx->getSelectColumns($class,$class);
			if (!empty($this->config['tvsSelect'])) {
				$select .= ', '.implode(',', $this->config['tvsSelect']);
			}
			$this->query->select($select);
			$this->addTime('Added selection of <b>'.$class.'</b>: <small>' . str_replace('`'.$class.'`.', '', $select) . '</small>');
		}
	}


	/**
	 * Group query by given field
	 *
	 */
	public function addGrouping() {
		if (!empty($this->config['groupby'])) {
			$groupby = $this->config['groupby'];
			$this->query->groupby($groupby);
			$this->addTime('Grouped by <b>'.$groupby.'</b>');
		}
	}


	/**
	 * Add sort to query
	 */
	public function addSort() {
		$tmp = (strpos($this->config['sortby'], '{') === 0)
			? $this->modx->fromJSON($this->config['sortby'])
			: array($this->config['sortby'] => $this->config['sortdir']);
		if (!empty($this->config['sortbyTV']) && !array_key_exists($this->config['sortbyTV'], $tmp)) {
			$tmp2[$this->config['sortbyTV']] = !empty($this->config['sortdirTV'])
				? $this->config['sortdirTV']
				: 'ASC';
			$tmp = array_merge($tmp2, $tmp);
		}
		$sorts = $this->replaceTVCondition($tmp);

		if (is_array($sorts)) {
			while (list($sortby, $sortdir) = each($sorts)) {
				if (preg_match_all('/TV(.*?)[`|.]/', $sortby, $matches)) {
					foreach ($matches[1] as $tv) {
						if (array_key_exists($tv,$this->config['tvsJoin'])) {
							$params = $this->config['tvsJoin'][$tv]['tv'];
							switch ($params['type']) {
								case 'number':
									$sortby = preg_replace('/(TV'.$tv.'\.value|`TV'.$tv.'`\.`value`)/', 'CAST($1 AS DECIMAL(10,3))', $sortby);
									break;
								case 'date':
									$sortby = preg_replace('/(TV'.$tv.'\.value|`TV'.$tv.'`\.`value`)/', 'CAST($1 AS DATETIME)', $sortby);
									break;
							}
						}
					}
				}
				$this->query->sortby($sortby, $sortdir);
				$this->addTime('Sorted by <b>'.$sortby.'</b>, <b>'.$sortdir.'</b>');
			}
		}
	}


	/**
	 * Set parameters and prepare query
	 *
	 * @return PDOStatement
	 */
	public function prepareQuery() {
		$this->addSort();
		$this->query->limit($this->config['limit'], $this->config['offset']);
		$this->addTime('Limited to <b>'.$this->config['limit'].'</b>, offset <b>'.$this->config['offset'].'</b>');

		return $this->query->prepare();
	}


	/**
	 * Add selection of template variables to query
	 *
	 */
	public function addTVs() {
		$includeTVs = $this->config['includeTVs'];
		$tvPrefix = $this->config['tvPrefix'];

		if (!empty($this->config['includeTVList']) && (empty($includeTVs) || is_numeric($includeTVs))) {
			$includeTVs = $this->config['includeTVList'];
		}
		if (!empty($this->config['sortbyTV'])) {
			$includeTVs .= empty($includeTVs)
				? $this->config['sortbyTV']
				: ','.$this->config['sortbyTV'];
		}

		if (!empty($includeTVs)) {
			$subclass = preg_grep('/^'.$this->config['class'].'/i' , $this->modx->classMap['modResource']);
			if (!preg_match('/^modResource$/i', $this->config['class']) && !count($subclass)) {
				$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Instantiated a derived class "'.$this->config['class'].'" that is not a subclass of the "modResource", so tvs not joining.');
			}
			else {
				$tvs = array_map('trim',explode(',',$includeTVs));
				$tvs = array_unique($tvs);
				if(!empty($tvs)) {
					$q = $this->modx->newQuery('modTemplateVar', array('name:IN' => $tvs));
					$q->select('id,name,type,default_text');
					if ($q->prepare() && $q->stmt->execute()) {
						$tvs = array();
						while ($tv = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
							$name = strtolower($tv['name']);
							$alias = 'TV'.$name;
							$this->config['tvsJoin'][$name] = array(
								'class' => 'modTemplateVarResource'
								,'alias' => $alias
								,'on' => '`TV'.$name.'`.`contentid` = `'.$this->config['class'].'`.`id` AND `TV'.$name.'`.`tmplvarid` = '.$tv['id']
								,'tv' => $tv
							);
							$this->config['tvsSelect'][$alias] = 'IFNULL(`'.$alias.'`.`value`, "'.$tv['default_text'].'") as `'.$tvPrefix.$tv['name'].'`';
							$tvs[] = $tv['name'];
						}
						$this->addTime('Included list of tvs: <b>'.implode(', ',$tvs).'</b>');
					}
				}
			}
		}
	}


	/**
	 * Convert tvFilters string to SQL and add to "where" condition
	 * This algorithm taken from snippet getResources by opengeek
	 *
	 */
	public function addTVFilters() {
		if (empty($this->config['tvFilters'])) {return;}
		$tvFiltersAndDelimiter = $this->config['tvFiltersAndDelimiter'];
		$tvFiltersOrDelimiter = $this->config['tvFiltersOrDelimiter'];

		$tvFilters = array_map('trim', explode($tvFiltersOrDelimiter, $this->config['tvFilters']));
		$operators = array(
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
			'=>' => '=>'
		);
		$conditions = array();

		$tmplVarTbl = $this->modx->getTableName('modTemplateVar');
		$tmplVarResourceTbl = $this->modx->getTableName('modTemplateVarResource');

		foreach ($tvFilters as $tvFilter) {
			$filterGroup = array();
			$filters = explode($tvFiltersAndDelimiter, $tvFilter);
			$multiple = count($filters) > 0;
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
				$tvValueField = 'tvr.value';
				$tvDefaultField = 'tv.default_text';
				$f = explode($operator, $filter);
				if (count($f) == 2) {
					$tvName = $this->modx->quote($f[0]);
					if (is_numeric($f[1]) && !in_array($sqlOperator, array('LIKE', 'NOT LIKE'))) {
						$tvValue = $f[1];
						if ($f[1] == (integer)$f[1]) {
							$tvValueField = "CAST({$tvValueField} AS SIGNED INTEGER)";
							$tvDefaultField = "CAST({$tvDefaultField} AS SIGNED INTEGER)";
						} else {
							$tvValueField = "CAST({$tvValueField} AS DECIMAL)";
							$tvDefaultField = "CAST({$tvDefaultField} AS DECIMAL)";
						}
					} else {
						$tvValue = $this->modx->quote($f[1]);
					}
					if ($multiple) {
						$filterGroup[] =
							"(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
							"OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
							")";
					} else {
						$filterGroup =
							"(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
							"OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
							")";
					}
				} elseif (count($f) == 1) {
					$tvValue = $this->modx->quote($f[0]);
					if ($multiple) {
						$filterGroup[] = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
					} else {
						$filterGroup = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
					}
				}
			}
			$conditions[] = $filterGroup;
		}

		if (!empty($conditions)) {
			$firstGroup = true;
			foreach ($conditions as $cGroup => $c) {
				if (is_array($c)) {
					$first = true;
					foreach ($c as $cond) {
						if ($first && !$firstGroup) {
							$this->query->condition($this->query->query['where'][0][1], $cond, xPDOQuery::SQL_OR, null, $cGroup);
						} else {
							$this->query->condition($this->query->query['where'][0][1], $cond, xPDOQuery::SQL_AND, null, $cGroup);
						}
						$first = false;
					}
				} else {
					$this->query->condition($this->query->query['where'][0][1], $c, $firstGroup ? xPDOQuery::SQL_AND : xPDOQuery::SQL_OR, null, $cGroup);
				}
				$firstGroup = false;
			}

			$this->addTime('Added TVs filters');
		}
	}


	/**
	 * Replaces tv fields to full name format.
	 * For example, field "test" will be replaced with "TVtest.value", if template variable "test" was joined in query.
	 *
	 */
	public function replaceTVCondition(array $array) {
		if (empty($this->config['tvsJoin'])) {return $array;}
		$tvs = implode('|', array_keys($this->config['tvsJoin']));

		$sorts = array();
		foreach ($array as $k => $v) {
			$callback = create_function('$matches', 'return \'`TV\'.strtolower($matches[1]).\'`.`value`\';');
			$tmp = preg_replace_callback('/\b('.$tvs.')\b/i', $callback, $k);
			$sorts[$tmp] = $v;
		}

		return $sorts;
	}


	/**
	 * Prepares fetched rows and process template variables.
	 */
	public function prepareResults(array $rows = array()) {
		if (!empty($this->config['includeTVs']) && (!empty($this->config['prepareTVs']) || !empty($this->config['processTVs']))) {
			$tvs = array_map('trim', explode(',', $this->config['includeTVs']));
			$prepare = ($this->config['prepareTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['prepareTVs']));
			$process = ($this->config['processTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['processTVs']));

			foreach ($rows as & $row) {
				foreach ($tvs as $tv) {
					if (!in_array($tv, $process) && !in_array($tv, $prepare)) {continue;}

					/** @var modTemplateVar $templateVar */
					if (!$templateVar = $this->getElement($tv, 'tv')) {
						if ($templateVar = $this->modx->getObject('modTemplateVar', array('name' => $tv))) {
							$this->addElement($tv, $templateVar, 'tv');
						}
					}

					if (!$templateVar) {
						$this->addTime('Could not process or prepare TV "'.$tv.'"');
						continue;
					}
					$key = $this->config['tvPrefix'].$templateVar->name;
					if (in_array($tv, $process)) {
						$row[$key] = $templateVar->renderOutput($row['id']);
					}
					elseif (in_array($tv, $prepare) && method_exists($templateVar, 'prepareOutput')) {
						$row[$key] = $templateVar->prepareOutput($row[$key]);
					}
				}
			}
			$this->addTime('Prepared and processed TVs');
		}

		//$this->addTime('Prepared fetched rows');

		return $rows;
	}

}
