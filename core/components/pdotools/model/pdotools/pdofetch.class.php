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
	public function setConfig(array $config = array()) {
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
			,'return' => 'chunks'	 // chunks, data or sql

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
			$this->config['sortby'] = $this->modx->getPK($this->config['class']);
		}

		$this->timings = array();
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

				if ($this->config['return'] == 'data') {
					$this->addTime('Returning raw data');
					$output = & $rows;
				}
				else {
					foreach ($rows as $v) {
						if (empty($this->config['tpl'])) {
							$output[] = '<pre>'.str_replace(array('[',']','`'), array('&#91;','&#93;','&#96;'), htmlentities(print_r($v, true), ENT_QUOTES, 'UTF-8')).'</pre>';
						}
						else {
							$output[] = $this->getChunk($this->config['tpl'], $v, $this->config['fastMode']);
						}
					}
					$this->addTime('Returning processed chunks');

					if (!empty($output)) {
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
		$q = $this->modx->newQuery($this->config['class']);
		$this->addTime('xPDO query object created');

		if (!empty($this->config['where'])) {
			$where = $this->modx->fromJson($this->config['where']);
			$q->where($where);

			$condition = array();
			foreach ($where as $k => $v) {
				if (is_array($v)) {$condition[] = $k.'('.implode(',',$v).')';}
				else {$condition[] = $k.'='.$v;}
			}
			$this->addTime('Added where condition: <b>' .implode(', ',$condition).'</b>');
		}

		$this->query = $q;
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
				if ($join == 'leftJoin') {
					// For backward compatibility with old snippets
					foreach ($tmp as $k => $v) {
						if (!empty($v['alias'])) {
							$tmp[$v['alias']] = $v;
							unset($tmp[$k]);
						}
					}
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
		if (!empty($this->config['select'])) {
			$tmp = array_merge($this->modx->fromJSON($this->config['select']), $this->config['tvsSelect']);
			$i = 0;
			foreach ($tmp as $k => $v) {
				if ($v == 'all' || $v == '*') {
					$v = $this->modx->getSelectColumns($k, $k);
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
	 * Group query by give field
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
	 * Set parameters and prepare query
	 *
	 * @return PDOStatement
	 */
	public function prepareQuery() {
		$limit = $this->config['limit'];
		$offset = $this->config['offset'];
		$sortby = $this->config['sortby'];
		$sortdir = $this->config['sortdir'];

		$this->query->limit($limit, $offset);
		$this->query->sortby($sortby, $sortdir);

		$this->addTime('Sorted by <b>'.$sortby.'</b>, <b>'.$sortdir.'</b>. Limited to <b>'.$limit.'</b>, offset <b>'.$offset.'</b>');
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

				if(!empty($tvs[0])){
					$q = $this->modx->newQuery('modTemplateVar', array('name:IN' => $tvs));
					$q->select('id,name');
					if ($q->prepare() && $q->stmt->execute()) {
						$tv_ids = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
						if (!empty($tv_ids)) {
							foreach ($tv_ids as $tv) {
								$alias = 'TV'.$tv['name'];
								$this->config['tvsJoin'][$alias] = array(
									'class' => 'modTemplateVarResource'
									,'alias' => $alias
									,'on' => 'TV'.$tv['name'].'.contentid='.$this->config['class'].'.id AND TV'.$tv['name'].'.tmplvarid='.$tv['id']
								);
								$this->config['tvsSelect'][$alias] = '`'.$alias.'`.`value` as `'.$tvPrefix.$tv['name'].'`';
							}
						}
					}
					$this->addTime('Included list of tvs: <b>'.implode(', ',$tvs).'</b>.');
				}
			}
		}
	}

}