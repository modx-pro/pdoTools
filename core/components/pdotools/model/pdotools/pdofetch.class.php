<?php
require_once 'pdotools.class.php';

class pdoFetch extends pdoTools {
	/* @var string $pk Primary key of class */
	protected $pk;
	/* @var array $ancestry Array with ancestors of class */
	protected $ancestry = array();
	/* @var xPDOQuery $query */
	protected $query;


	/**
	 * {@inheritdoc}
	 */
	public function setConfig(array $config = array(), $clean_timings = true) {
		parent::setConfig(
			array_merge(array(
				'class' => 'modResource',
				'limit' => 10,
				'offset' => 0,
				'sortby' => '',
				'sortdir' => 'DESC',
				'groupby' => '',
				'totalVar' => 'total',
				'tpl' => '',
				'return' => 'chunks',	// chunks, data, sql or ids

				'select' => '',
				'leftJoin' => '',
				'rightJoin' => '',
				'innerJoin' => '',

				'includeTVs' => '',
				'tvPrefix' => '',
				'tvsJoin' => array(),
				'tvsSelect' => array(),

				'tvFiltersAndDelimiter' => ',',
				'tvFiltersOrDelimiter' => '||',
			), $config)
		, $clean_timings);

		$this->pk = $this->modx->getPK($this->config['class']);
		$this->ancestry = $this->modx->getAncestry($this->config['class']);
		$this->idx = !empty($this->config['offset'])
			? (integer) $this->config['offset'] + 1
			: 1;
	}


	/**
	 * Main method for query processing and fetching rows
	 * It can return string with SQL query, array or raw rows or processed HTML chunks
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
		$this->prepareQuery();

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
				$rows = $this->checkPermissions($rows);
				$this->count = count($rows);

				if (strtolower($this->config['return']) == 'ids') {
					$ids = array();
					foreach ($rows as $row) {
						$ids[] = $row['id'];
					}
					$output = implode(',', $ids);
				}
				elseif (strtolower($this->config['return']) == 'data') {
					$rows = $this->prepareRows($rows);
					$this->addTime('Returning raw data');
					$output = & $rows;
				}
				else {
					$rows = $this->prepareRows($rows);
					foreach ($rows as $row) {
						$row = array_merge(
							$this->config,
							$row,
							array('idx' => $this->idx++)
						);
						$tpl = $this->defineChunk($row);
						if (empty($tpl)) {
							$output[] = '<pre>'.$this->getChunk('', $row).'</pre>';
						}
						else {
							$output[] = $this->getChunk($tpl, $row, $this->config['fastMode']);
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
				$this->addTime('Could not process query, error #'.$errors[1].': ' .$errors[2]);
			}
		}
		return $output;
	}


	/**
	 * Create object with xPDOQuery
	 */
	public function makeQuery() {
		$this->query = $this->modx->newQuery($this->config['class']);
		$this->addTime('xPDO query object created');
	}


	/**
	 * Adds where and having conditions
	 */
	public function addWhere() {
		$this->addTVFilters();

		$where = array();
		if (!empty($this->config['where'])) {
			$tmp = $this->config['where'];
			if (!is_array($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
				$tmp = $this->modx->fromJSON($tmp);
			}
			$where = $this->replaceTVCondition($tmp);
		}
		$where = $this->additionalConditions($where);
		if (!empty($where)) {
			$this->query->where($where);
			$condition = array();
			foreach ($where as $k => $v) {
				if (is_array($v)) {
					if (isset($v[0])) {
						$condition[] = is_array($v) ? $k.'('.implode(',',$v).')' : $k.'='.$v;
					}
					else {
						foreach ($v as $k2 => $v2) {
							$condition[] = is_array($v2) ? $k2.'('.implode(',',$v2).')' : $k2.'='.$v2;
						}
					}
				}
				else {$condition[] = $k.'='.$v;}
			}
			$this->addTime('Added where condition: <b>' .implode(', ',$condition).'</b>');
		}
		if (!empty($this->config['having'])) {
			$tmp = $this->config['having'];
			if (!is_array($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
				$tmp = $this->modx->fromJSON($tmp);
			}
			$having = $this->replaceTVCondition($tmp);
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
	 */
	public function addJoins() {
		// left join is always needed because of TVs
		if (empty($this->config['leftJoin'])) {
			$this->config['leftJoin'] = '[]';
		}

		foreach (array('innerJoin','leftJoin','rightJoin') as $join) {
			if (!empty($this->config[$join])) {
				$tmp = $this->config[$join];
				if (!is_array($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
					$tmp = $this->modx->fromJSON($tmp);
				}
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
	 */
	public function addSelects() {
		if ($this->config['return'] == 'ids') {
			$this->query->select('
				SQL_CALC_FOUND_ROWS `'.$this->config['class'].'`.`'.$this->pk.'`
			');
			$this->addTime('Parameter "return" set to "ids", so we select only primary key');
		}
		elseif ($tmp = $this->config['select']) {
			if (!is_array($tmp)) {
				$tmp = ($tmp[0] == '{' || $tmp[0] == '[')
					? $this->modx->fromJSON($tmp)
					: array($this->config['class'] => $tmp);
			}
			$tmp = array_merge($tmp, $this->config['tvsSelect']);
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
				if (is_array($v)) {
					$v = current($v) . ' AS ' . current(array_flip($v));
				}
				$this->addTime('Added selection of <b>'.$k.'</b>: <small>' . str_replace('`'.$k.'`.', '', $v) . '</small>');
				$i++;
			}
		}
		else {
			$columns = array_keys($this->modx->getFieldMeta($this->config['class']));
			if (isset($this->config['includeContent']) && empty($this->config['includeContent'])) {
				$key = array_search('content', $columns);
				unset($columns[$key]);
			}
			$this->config['select'] = array($this->config['class'] => implode(',', $columns));
			$this->addSelects();
		}
	}

	/**
	 * Group query by given field
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
		$tmp = $this->config['sortby'];
		if (empty($tmp)) {
			$resources = $this->config['class'].'.'.$this->pk.':IN';
			if (!empty($this->config['where'][$resources])) {
				$tmp = array(
					'find_in_set(`'.$this->config['class'].'`.`'.$this->pk.'`,\''.implode(',', $this->config['where'][$resources]).'\')' => ''
				);
			}
			else {
				$tmp = array(
					$this->config['class'].'.'.$this->pk => !empty($this->config['sortdir'])
						? $this->config['sortdir']
						: 'ASC'
				);
			}
		}
		else {
			$tmp = (!is_array($tmp) && ($tmp[0] == '{' || $tmp[0] == '['))
				? $this->modx->fromJSON($this->config['sortby'])
				: array($this->config['sortby'] => $this->config['sortdir']);
		}
		if (!empty($this->config['sortbyTV']) && !array_key_exists($this->config['sortbyTV'], $tmp['sortby'])) {
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
		if (!empty($this->config['limit'])) {
			$this->query->limit($this->config['limit'], $this->config['offset']);
			$this->addTime('Limited to <b>'.$this->config['limit'].'</b>, offset <b>'.$this->config['offset'].'</b>');
		}

		return $this->query->prepare();
	}


	/**
	 * Add selection of template variables to query
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
							$this->config['tvsSelect'][$alias] = array('`'.$tvPrefix.$tv['name'].'`' => 'IFNULL(`'.$alias.'`.`value`, '.$this->modx->quote($tv['default_text']).')');
							$tvs[] = $tv['name'];
						}
						$this->addTime('Included list of tvs: <b>'.implode(', ',$tvs).'</b>');
					}
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
	public function additionalConditions($where = array()) {
		$config = $this->config;
		$class = $this->config['class'];

		// These rules works only for descendants of modResource
		if (!in_array('modResource', $this->ancestry) || !empty($config['disableConditions'])) {
			return $where;
		}

		$params = array(
			'resources' => 'id',
			'parents' => 'parent',
			'templates' => 'template',
			'showUnpublished' => 'published',
			'showHidden' => 'hidemenu',
			'showDeleted' => 'deleted',
			'hideContainers' => 'isfolder',
			'hideUnsearchable' => 'searchable',
			'context' => 'context_key',
		);

		// Exclude parameters that may already have been processed
		foreach ($params as $param => $field) {
			$found = false;
			if (isset($config[$param])) {
				foreach ($where as $k => $v) {
					// Usual condition
					if (!is_numeric($k) && strpos($k, $field) === 0 || strpos($k, $class.'.'.$field) !== false) {
						$found = true;
						break;
					}
					// Array of conditions
					elseif (is_numeric($k) && is_array($v)) {
						foreach ($v as $k2 => $v2) {
							if (strpos($k2, $field) === 0 || strpos($k2, $class.'.'.$field) !== false) {
								$found = true;
								break(2);
							}
						}
					}
					// Raw SQL string
					elseif (is_numeric($k) && strpos($v, $class) !== false && preg_match('/\b'.$field.'\b/i', $v)) {
						$found = true;
						break;
					}
				}
				if ($found) {
					unset($params[$param]);
				}
				else {
					$params[$param] = $config[$param];
				}
			}
			else {
				unset($params[$param]);
			}
		}

		// Process the remaining parameters
		foreach ($params as $param => $value) {
			switch ($param) {
				case 'showUnpublished':
					if (empty($value)) {
						$where[$class.'.published'] = 1;
					}
					break;
				case 'showHidden':
					if (empty($value)) {
						$where[$class.'.hidemenu'] = 0;
					}
					break;
				case 'showDeleted':
					if (empty($value)) {
						$where[$class.'.deleted'] = 0;
					}
					break;
				case 'hideContainers':
					if (!empty($value)) {
						$where[$class.'.isfolder'] = 0;
					}
					break;
				case 'hideUnsearchable':
					if (!empty($value)) {
						$where[$class.'.searchable'] = 1;
					}
					break;
				case 'context':
					if (!empty($value)) {
						$context = array_map('trim', explode(',', $value));
						if (!empty($context) && is_array($context)) {
							if (count($context) == 1) {
								$where[$class.'.context_key'] = $context[0];
							}
							else {
								$where[$class.'.context_key:IN'] = $context;
							}
						}
					}
					break;
				case 'resources':
					if (!empty($value)) {
						$resources = array_map('trim', explode(',', $value));
						$resources_in = $resources_out = array();
						foreach ($resources as $v) {
							if (!is_numeric($v)) {continue;}
							if ($v < 0) {$resources_out[] = abs($v);}
							else {$resources_in[] = abs($v);}
						}
						if (!empty($resources_in)) {
							$where[$class.'.id:IN'] = $resources_in;
						}
						if (!empty($resources_out)) {
							$where[$class.'.id:NOT IN'] = $resources_out;
						}
					}
					break;
				case 'parents':
					if (!empty($value)) {
						$parents = array_map('trim', explode(',', $value));
						$parents_in = $parents_out = array();
						foreach ($parents as $v) {
							if (!is_numeric($v)) {continue;}
							if ($v[0] == '-') {$parents_out[] = abs($v);}
							else {$parents_in[] = abs($v);}
						}
						$depth = (isset($config['depth']) && $config['depth'] !== '')
							? (integer) $config['depth']
							: 10;
						if (!empty($depth) && $depth > 0) {
							$pids = array();
							$q = $this->modx->newQuery($class, array('id:IN' => array_merge($parents_in, $parents_out)));
							$q->select('id,context_key');
							if ($q->prepare() && $q->stmt->execute()) {
								while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
									$pids[$row['id']] = $row['context_key'];
								}
							}
							foreach ($pids as $k => $v) {
								if (!is_numeric($k)) {continue;}
								elseif (in_array($k, $parents_in)) {
									$parents_in = array_merge($parents_in, $this->modx->getChildIds($k, $depth, array('context' => $v)));
								}
								else {
									$parents_out = array_merge($parents_out, $this->modx->getChildIds($k, $depth, array('context' => $v)));
								}
							}
						}
						// Support of miniShop2 categories
						$members = array();
						if (strpos($this->modx->config['extension_packages'], 'minishop2') !== false) {
							if (!empty($parents_in) || !empty($parents_out)) {
								$q = $this->modx->newQuery('msCategoryMember');
								if (!empty($parents_in)) {$q->where(array('category_id:IN' => $parents_in));}
								if (!empty($parents_out)) {$q->where(array('category_id:NOT IN' => $parents_out));}
								$q->select('product_id');
								if ($q->prepare() && $q->stmt->execute()) {
									$members = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
								}
							}
						}
						// Add parent to conditions
						if (!empty($parents_in) && !empty($members)) {
							$where[] = array(
								$class.'.parent:IN' => $parents_in,
								'OR:'.$class.'.id:IN' => $members
							);
						}
						elseif (!empty($parents_in)) {
							$where[$class.'.parent:IN'] = $parents_in;
						}
						if (!empty($parents_out)) {
							$where[$class.'.parent:NOT IN'] = $parents_out;
						}
					}
					break;
				case 'templates':
					if (!empty($value)) {
						$templates = array_map('trim', explode(',', $value));
						$templates_in = $templates_out = array();
						foreach ($templates as $v) {
							if (!is_numeric($v)) {continue;}
							if ($v[0] == '-') {$templates_out[] = abs($v);}
							else {$templates_in[] = abs($v);}
						}
						if (!empty($templates_in)) {$where[$class.'.template:IN'] = $templates_in;}
						if (!empty($templates_out)) {$where[$class.'.template:NOT IN'] = $templates_out;}
					}
					break;
			}
		}
		$this->addTime('Processed additional conditions');
		$this->config['where'] = $where;
		return $where;
	}


	/**
	 * Convert tvFilters string to SQL and add to "where" condition
	 * This algorithm taken from snippet getResources by opengeek
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
	 * @param array $array Array for replacement
	 *
	 * @return array $sorts Array with replaced conditions
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
	 * Simple and quick replacement for modX::getObject()
	 *
	 * @param $class
	 * @param string $where
	 * @param array $config
	 *
	 * @return array
	 */
	public function getObject($class, $where = '', $config = array()) {
		if (!empty($config['loadModels'])) {$this->config['loadModels'] = $config['loadModels'];}
		$this->loadModels();

		$config['class'] = $class;
		$config['limit'] = 1;
		if (!empty($where)) {
			unset($config['where']);
			if (is_numeric($where)) {
				$where = array($this->modx->getPK($class) => (integer) $where);
			}
			elseif (is_scalar($where) && ($where[0] == '{' || $where[0] == '[')) {
				$where = $this->modx->fromJSON($where);
			}
			if (is_array($where)) {
				$config['where'] = $where;
			}
		}

		$this->setConfig($config, true);
		$this->makeQuery();
		$this->addTVs();
		$this->addJoins();
		$this->addGrouping();
		$this->addSelects();
		$this->addWhere();

		$this->query->prepare();
		$this->addTime('SQL prepared <small>"'.$this->query->toSql().'"</small>');

		$row = array();
		if ($this->query->stmt->execute()) {
			$row = $this->query->stmt->fetch(PDO::FETCH_ASSOC);
		}
		else {
			$errors = $this->query->stmt->errorInfo();
			$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Could not load object "'.$class.'": Error '.$errors[0].': '.$errors[2]);
			$this->addTime('Could not process query, error #'.$errors[1].': ' .$errors[2]);

		}

		return $row;
	}


	/**
	 * Simple and quick replacement for modX::getCollection()
	 *
	 * @param $class
	 * @param string $where
	 * @param array $config
	 *
	 * @return array
	 */
	public function getCollection($class, $where = '', $config = array()) {
		if (!empty($config['loadModels'])) {$this->config['loadModels'] = $config['loadModels'];}
		$this->loadModels();

		$this->config['class'] = $class;
		$config['limit'] = !isset($config['limit']) ? 0 : (integer) $config['limit'];
		if (!empty($where)) {
			unset($config['where']);
			if (is_numeric($where)) {
				$where = array($this->modx->getPK($class) => (integer) $where);
			}
			elseif (is_scalar($where) && ($where[0] == '{' || $where[0] == '[')) {
				$where = $this->modx->fromJSON($where);
			}
			if (is_array($where)) {
				$config['where'] = $where;
			}
		}

		$this->setConfig($config, true);
		$this->makeQuery();
		$this->addTVs();
		$this->addJoins();
		$this->addGrouping();
		$this->addSelects();
		$this->addWhere();
		$this->prepareQuery();

		$this->addTime('SQL prepared <small>"'.$this->query->toSql().'"</small>');

		$rows = array();
		if ($this->query->stmt->execute()) {
			$rows = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);

			$q = $this->modx->prepare("SELECT FOUND_ROWS();");
			$q->execute();
			$total = $q->fetch(PDO::FETCH_COLUMN);
			$this->addTime('Total rows: <b>'.$total.'</b>');

			$rows = $this->checkPermissions($rows);
		}
		else {
			$errors = $this->query->stmt->errorInfo();
			$this->modx->log(modX::LOG_LEVEL_ERROR, '[pdoTools] Could not load collection of "'.$class.'": Error '.$errors[0].': '.$errors[2]);
			$this->addTime('Could not process query, error #'.$errors[1].': ' .$errors[2]);
		}

		return $rows;
	}

}
