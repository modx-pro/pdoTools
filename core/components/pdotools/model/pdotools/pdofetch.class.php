<?php
require_once 'pdotools.class.php';

class pdoFetch extends pdoTools {

	/* @var xPDOQuery_mysql $query */
	private $query;

	public function __construct(modX $modx, array $config = array()) {
		parent::__construct($modx);

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

				,'nestedChunkPrefix' => 'pdotools_'
			),$config
		);

		if (empty($this->config['sortby'])) {
			$this->config['sortby'] = $this->modx->getPK($this->config['class']);
		}
	}


	public function run() {
		$this->makeQuery();
		$this->addJoins();
		$this->addGrouping();
		$this->addSelects();

		if (!$this->prepareQuery()) {return false;}

		$output = null;
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
					foreach ($rows as $k => $v) {
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
		$this->modx->setPlaceholder('pdoFetchLog', $this->getTime());
		return $output;
	}


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


	public function setTotal() {
		if ($this->config['return'] != 'sql') {
			$q = $this->modx->prepare("SELECT FOUND_ROWS();");
			$q->execute();
			$total = $q->fetch(PDO::FETCH_COLUMN);
			$this->addTime('Total rows: <b>'.$total.'</b>');
			$this->modx->setPlaceholder($this->config['totalVar'], $total);
		}
	}


	public function addJoins() {
		foreach (array('innerJoin','leftJoin','rightJoin') as $join) {
			if (!empty($this->config[$join])) {
				$tmp = $this->modx->fromJson($this->config[$join]);
				foreach ($tmp as $k => $v) {
					$class = !empty($v['class']) ? $v['class'] : $k;
					$this->query->$join($class, $v['alias'], $v['on']);
					$this->addTime($join.'ed <i>'.$class.'</i> as <b>'.$v['alias'].'</b>');
				}
			}
		}
	}


	public function addSelects() {
		if (!empty($this->config['select'])) {
			$tmp = $this->modx->fromJSON($this->config['select']);
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
			$this->query->select($select);
			$this->addTime('Added selection of <b>'.$class.'</b>: <small>' . $select . '</small>');
		}
	}


	public function addGrouping() {
		if (!empty($this->config['groupby'])) {
			$groupby = $this->config['groupby'];
			$this->query->groupby($groupby);
			$this->addTime('Grouped by <b>'.$groupby.'</b>');
		}
	}

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
}