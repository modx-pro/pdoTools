<?php
require_once 'pdotools.class.php';

class pdoFetch extends pdoTools {
	/* @var xPDOQuery_mysql $query */
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

			,'nestedChunkPrefix' => 'pdotools_'
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
				else if (strtolower($this->config['return']) == 'data') {
					$this->addTime('Returning raw data');
					$output = & $rows;
				}
				else {
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
		else if (!empty($this->config['select'])) {
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
				$this->addTime('Added selection of <b>'.$k.'</b>: <small>' . $v . '</small>');
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
			$this->addTime('Added selection of <b>'.$class.'</b>: <small>' . $select . '</small>');
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
		$sorts = $this->replaceTVCondition($tmp);

		if (is_array($sorts)) {
			while (list($sortby, $sortdir) = each($sorts)) {
				if (preg_match_all('/TV(.*?)[`|.]/', $sortby, $matches)) {
					foreach ($matches[1] as $tv) {
						if (array_key_exists($tv,$this->config['tvsJoin'])) {
							$params = $this->config['tvsJoin'][$tv]['tv'];
							switch ($params['type']) {
								case 'number':
									$sortby = preg_replace('/(TV'.$tv.'\.value|`TV'.$tv.'`\.`value`)/', 'CAST($1 AS DECIMAL)', $sortby);
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

		if (!empty($includeTVs)) {
			$subclass = preg_grep('/^'.$this->config['class'].'/i' , $this->modx->classMap['modResource']);
			if (!preg_match('/^modResource$/i', $this->config['class']) && !count($subclass)) {
				$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Instantiated a derived class "'.$this->config['class'].'" that is not a subclass of the "modResource", so tvs not joining.');
			}
			else {
				$tvs = array_map('trim',explode(',',$includeTVs));

				if(!empty($tvs[0])) {
					$q = $this->modx->newQuery('modTemplateVar', array('name:IN' => $tvs));
					$q->select('id,name,type,default_text');
					if ($q->prepare() && $q->stmt->execute()) {
						$tvs = array();
						while ($tv = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
							$alias = 'TV'.$tv['name'];
							$name = strtolower($tv['name']);
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
	 * Replaces tv fields to full name format.
	 * For example, field "test" will be replaced with "TVtest.value", if template variable "test" was joined in query.
	 *
	 */
	public function replaceTVCondition(array $array) {
		$tvs = implode('|', array_keys($this->config['tvsJoin']));

		if (!empty($tvs)) {
			foreach ($array as $k => $v) {
				$tmp = preg_replace('/\b('.$tvs.')\b/i', '`TV$1`.`value`', $k);
				if ($tmp != $k) {
					$array[$tmp] = $v;
					unset($array[$k]);
				}
			}
		}

		return $array;
	}

}
