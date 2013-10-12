<?php

class pdoTools {
	/** @var modX $modx */
	public $modx;
	/** @var array $timings Array with query log */
	public $timings = array();
	/** @var array $config Array with class config */
	public $config = array();
	/** @var array $store Array for cache elements and user data */
	public $store = array(
		'chunk' => array(),
		'snippet' => array(),
		'tv' => array(),
		'data' => array(),
	);
	/** @var integer $idx Index of iterator of rows processing */
	public $idx = 1;
	/** @var integer $time Time of script start */
	protected $time;
	/** @var integer $count Total number of results for chunks processing */
	protected $count = 0;
	/** @var boolean $preparing Specifies that now is the preparation */
	protected $preparing = false;


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX & $modx, $config = array()) {
		$this->modx = $modx;
		$this->time = microtime(true);

		$this->setConfig($config);
	}


	/**
	 * Set config from default values and given array.
	 *
	 * @param array $config
	 * @param bool $clean_timings Clean timings array
	 */
	public function setConfig(array $config = array(), $clean_timings = true) {
		$this->config = array_merge(array(
			'fastMode' => false,
			'nestedChunkPrefix' => 'pdotools_',

			'checkPermissions' => '',
			'loadModels' => '',
			'prepareSnippet' => '',

			'outputSeparator' => "\n",
		), $config);

		if ($clean_timings) {
			$this->timings = array();
		}
	}


	/**
	 * Add new record to timings log
	 *
	 * @param $message
	 */
	public function addTime($message) {
		$time = microtime(true);
		$this->timings[] = array(
			'time' =>  number_format(round(($time - $this->time), 7), 7)
			,'message' => $message
		);
		$this->time = $time;
	}


	/**
	 * Return timings log
	 *
	 * @param bool $string Return array or formatted string
	 *
	 * @return array|string
	 */
	public function getTime($string = true) {
		if (!$string) {
			return $this->timings;
		}
		else {
			$res = $sum = null;
			foreach ($this->timings as $v) {
				$res .= $v['time'] . ': ' . $v['message'] . "\n";
				$sum += $v['time'];
			}

			$res .= number_format(round($sum, 7), 7) . ": <b>Total time</b>\n";
			$res .= number_format(round((memory_get_usage(true)), 2), 0, ',', ' ').': <b>Memory usage</b>';
			return $res;
		}
	}


	/**
	 * Set data to cache
	 *
	 * @param $name
	 * @param $object
	 * @param string $type
	 */
	public function setStore($name, $object, $type = 'data') {
		$this->store[$type][$name] = $object;
	}


	/**
	 * Get data from cache
	 *
	 * @param $name
	 * @param string $type
	 *
	 * @return mixed|null
	 */
	public function getStore($name, $type = 'data') {
		return isset($this->store[$type][$name])
			? $this->store[$type][$name]
			: null;
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
	 * Transform array to placeholders
	 *
	 * @param array $array
	 * @param string $prefix
	 *
	 * @return array
	 */
	public function makePlaceholders(array $array = array(), $prefix = '') {
		$result = array(
			'pl' => array()
			,'vl' => array()
		);
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$result = array_merge_recursive($result, $this->makePlaceholders($v, $k.'.'));
			}
			else {
				$result['pl'][$prefix.$k] = '[[+'.$prefix.$k.']]';
				$result['pl']['!'.$prefix.$k] = '[[!+'.$prefix.$k.']]';
				$result['vl'][$prefix.$k] = $v;
				$result['vl']['!'.$prefix.$k] = $v;
			}
		}

		return $result;
	}


	/**
	 * Process and return the output from a Chunk by name.
	 *
	 * @param string $name The name of the chunk.
	 * @param array $properties An associative array of properties to process the Chunk with, treated as placeholders within the scope of the Element.
	 * @param boolean $fastMode If false, all MODX tags in chunk will be processed.
	 *
	 * @return mixed The processed output of the Chunk.
	 */
	public function getChunk($name = '', array $properties = array(), $fastMode = false) {
		$properties = $this->prepareRow($properties);
		$name = trim($name);

		/* @var $chunk modChunk[] */
		if (!empty($name)) {
			$chunk = $this->_loadChunk($name, $properties);
		}
		if (empty($name) || empty($chunk) || !($chunk['object'] instanceof modChunk)) {
			return str_replace(array('[',']','`'), array('&#91;','&#93;','&#96;'), htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'));
		}

		$chunk['object']->_cacheable = false;
		$chunk['object']->_processed = false;
		$chunk['object']->_content = '';

		// Processing quick placeholders
		if (!empty($chunk['placeholders'])) {
			$pl = $chunk['placeholders'];
			foreach ($pl as $k => $v) {
				if (empty($properties[$k])) {
					$pl[$k] = '';
				}
			}
			$pl = $this->makePlaceholders($pl);
			$chunk['content'] = str_replace($pl['pl'], $pl['vl'], $chunk['content']);
		}

		// Processing standard placeholders
		$pl = $this->makePlaceholders($properties);
		$content = str_replace($pl['pl'], $pl['vl'], $chunk['content']);

		// Processing lexicon placeholders
		preg_match_all('/\[\[(%|~)(.*?)\]\]/', $content, $matches);
		$src = $dst = array();
		$scheme = $this->modx->getOption('link_tag_scheme', -1, true);
		foreach ($matches[2] as $k => $v) {
			if ($matches[1][$k] == '%') {
				$tmp = $this->modx->lexicon($v);
				if ($tmp != $v) {
					$src[] = $matches[0][$k];
					$dst[] = $tmp;
				}
			}
			elseif ($matches[1][$k] == '~' && is_numeric($v)) {
				if ($tmp = $this->modx->makeUrl($v, '', '', $scheme)) {
					$src[] = $matches[0][$k];
					$dst[] = $tmp;
				}
			}
		}
		if (!empty($src) && !empty($dst)) {
			$content = str_replace($src, $dst, $content);
		}

		$output = $fastMode
			? $this->fastProcess($content, $properties)
			: $chunk['object']->process($properties, $content);

		return $output;
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
	public function parseChunk($name = '', array $properties = array(), $prefix = '[[+', $suffix = ']]') {
		$properties = $this->prepareRow($properties);
		$name = trim($name);

		if (!empty($name)) {
			$chunk = $this->_loadChunk($name, $properties);
		}
		if (empty($name) || empty($chunk['content'])) {
			return str_replace(array('[',']','`'), array('&#91;','&#93;','&#96;'), htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'));
		}

		$output = $chunk['content'];
		$tmp = array();
		foreach ($properties as $key => $value) {
			$tmp[] = $prefix.$key.$suffix;
		}

		return str_replace($tmp, $properties, $output);
	}


	/**
	 * Fast processing of MODX tags. All unprocessed tags will be cut.
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function fastProcess($content) {
		if (!$this->modx->parser) {$this->modx->getParser();}
		$matches = array();
		$this->modx->parser->collectElementTags($content, $matches);
		$tags = array('pl' => array(), 'vl' => array());
		foreach ($matches as $v) {
			$tags['pl'][] = $v[0];
			if (strpos($v[1], '+') === 0) {
				$v[1] = substr($v[1], 1);
			}
			else if (strpos($v[1], '!+') === 0) {
				$v[1] = substr($v[1], 2);
			}
			$tags['vl'][] = !empty($v[1]) && isset($this->modx->placeholders[$v[1]])
				? $this->modx->placeholders[$v[1]]
				: '';
		}
		$content = str_replace($tags['pl'], $tags['vl'], $content);

		return $content;
	}


	/**
	 * Fast processing of some MODX elements: snippets and simple output filters
	 * @param $tag
	 * @param array $properties
	 * @param string $token
	 *
	 * @disabled
	 *
	 * @return mixed|string
	 */
	/*
	public function fastElement($tag, $properties = array(), $token = '+') {
		$scriptProperties = array();

		// Snippet with parameters
		if ($pos = strpos($tag, '?')) {
			$name = substr($tag, 0, $pos);
			$scriptProperties = $this->modx->parser->parseProperties(substr($tag, $pos + 1));
		}
		// Filter without options
		elseif (strpos($tag, ':') !== false) {
			$tmp = explode(':', $tag);
			$scriptProperties['input'] = $tmp[0];
			$scriptProperties['name'] = $name = $tmp[1];
			$scriptProperties['options'] = null;
		}
		// Filter with options
		elseif (preg_match('/^(.*?):(.*)(?:=`(.*?)`)$/', $tag, $matches)) {
			$scriptProperties['input'] = $matches[1];
			$scriptProperties['name'] = $name = $matches[2];
			$scriptProperties['options'] = $matches[3];
		}
		// Snippet without parameters
		else {
			$name = $tag;
		}

		if (!$element = $this->getStore($name, 'snippet')) {
			if ($element = $this->modx->getObject('modSnippet', array('name' => $name))) {
				$this->setStore($name, $element, 'snippet');
			}
			else {
				$this->addTime('Could not load snippet "'.$name.'".');
				return '';
			}
		}
		$element->_cacheable = false;
		$element->_processed = false;

		if ($token == '+') {
			$scriptProperties['tag'] = '[[+'.$tag.']]';

			if (array_key_exists($scriptProperties['input'], $properties)) {
				$scriptProperties['input'] = $properties[$scriptProperties['input']];
			}
			elseif (array_key_exists($scriptProperties['input'], $this->modx->placeholders)) {
				$scriptProperties['input'] = $this->modx->placeholders[$scriptProperties['input']];
			}
			else {
				return '';
			}
		}

		return $element->process($scriptProperties);
	}



	/**
	 * Method for define name of a chunk serving as resource template
	 * This algorithm taken from snippet getResources by opengeek
	 *
	 * @param array $properties Resource fields
	 *
	 * @return mixed
	 */
	public function defineChunk($properties = array()) {
		$idx = isset($properties['idx']) ? (integer) $properties['idx'] : $this->idx++;
		$first = empty($this->config['first']) ? ($this->config['offset'] + 1) : (integer) $this->config['first'];
		$last = empty($this->config['last']) ? ($this->count + $this->config['offset']) : (integer) $this->config['last'];

		$odd = !($idx & 1);
		$resourceTpl = '';
		if ($idx == $first && !empty($this->config['tplFirst'])) {
			$resourceTpl = $this->config['tplFirst'];
		}
		else if ($idx == $last && !empty($this->config['tplLast'])) {
			$resourceTpl = $this->config['tplLast'];
		}
		else if (!empty($this->config['tpl_' . $idx])) {
			$resourceTpl = $this->config['tpl_' . $idx];
		}
		else if ($idx > 1) {
			$divisors = array();
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
		}
		else if (empty($resourceTpl) && !empty($this->config['tplCondition']) && !empty($this->config['conditionalTpls'])) {
			$conTpls = $this->modx->fromJSON($this->config['conditionalTpls']);
			if (isset($properties[$this->config['tplCondition']])) {
				$subject = $properties[$this->config['tplCondition']];
				$tplOperator = !empty($this->config['tplOperator']) ? strtolower($this->config['tplOperator']) : '=';
				$tplCon = '';
				foreach ($conTpls as $operand => $conditionalTpl) {
					switch ($tplOperator) {
						case '!=': case 'neq': case 'not': case 'isnot': case 'isnt': case 'unequal': case 'notequal':
							$tplCon = (($subject != $operand) ? $conditionalTpl : $tplCon);
							break;
						case '<': case 'lt': case 'less': case 'lessthan':
							$tplCon = (($subject < $operand) ? $conditionalTpl : $tplCon);
							break;
						case '>': case 'gt': case 'greater': case 'greaterthan':
							$tplCon = (($subject > $operand) ? $conditionalTpl : $tplCon);
							break;
						case '<=': case 'lte': case 'lessthanequals': case 'lessthanorequalto':
							$tplCon = (($subject <= $operand) ? $conditionalTpl : $tplCon);
							break;
						case '>=': case 'gte': case 'greaterthanequals': case 'greaterthanequalto':
							$tplCon = (($subject >= $operand) ? $conditionalTpl : $tplCon);
							break;
						case 'isempty': case 'empty':
							$tplCon = empty($subject) ? $conditionalTpl : $tplCon;
							break;
						case '!empty': case 'notempty': case 'isnotempty':
							$tplCon = !empty($subject) && $subject != '' ? $conditionalTpl : $tplCon;
							break;
						case 'isnull': case 'null':
							$tplCon = $subject == null || strtolower($subject) == 'null' ? $conditionalTpl : $tplCon;
							break;
						case 'inarray': case 'in_array': case 'ia':
							$operand = explode(',', $operand);
							$tplCon = in_array($subject, $operand) ? $conditionalTpl : $tplCon;
							break;
						case 'between': case 'range': case '>=<': case '><':
							$operand = explode(',', $operand);
							$tplCon = ($subject >= min($operand) && $subject <= max($operand)) ? $conditionalTpl : $tplCon;
							break;
						case '==': case '=': case 'eq': case 'is': case 'equal': case 'equals': case 'equalto':
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
	 * @param array $row Current row with results being processed
	 *
	 * @return array
	 */
	protected function _loadChunk($name, $row = array()) {
		$binding = $content = '';

		$name = trim($name);
		if (preg_match('/^@([A-Z]+)/', $name, $matches)) {
			$binding = $matches[1];
			$content = substr($name, strlen($binding) + 1);
		}
		$content = ltrim($content, ' :');

		// Change name for empty TEMPLATE binding so will be used template of given row
		if ($binding == 'TEMPLATE' && empty($content) && isset($row['template'])) {
			$name = '@TEMPLATE '.$row['template'];
			$content = $row['template'];
		}

		// Load from cache
		$cache_name = (strpos($name, '@') === 0) ? md5($name) : $name;
		if ($chunk = $this->getStore($cache_name, 'chunk')) {
			return $chunk;
		}

		/** @var modChunk $element */
		switch ($binding) {
			case 'CODE':
			case 'INLINE':
				$element = $this->modx->newObject('modChunk', array('name' => md5($name)));
				$element->setContent($content);
				$this->addTime('Created inline chunk');
				break;
			case 'FILE':
				$path = !empty($this->config['tplPath'])
					? $this->config['tplPath'] . '/'
					: MODX_ASSETS_PATH . 'elements/chunks/';
				$path = (strpos($content, MODX_BASE_PATH) === false)
					? MODX_BASE_PATH . ltrim($path, '/') . $content
					: $content;
				$path = preg_replace('#/+#', '/', $path);

				if (!preg_match('/(.html|.tpl)$/i', $path)) {
					$this->addTime('Allowed extensions for @FILE chunks is "html" and "tpl"');
				}
				elseif ($content = file_get_contents($path)) {
					$element = $this->modx->newObject('modChunk', array('name' => md5($name)));
					$element->setContent($content);
					$this->addTime('Loaded chunk from "'.str_replace(MODX_BASE_PATH, '', $path).'"');
				}
				break;
			case 'TEMPLATE':
				/** @var modTemplate $template */
				if ($template = $this->modx->getObject('modTemplate', array('id' => $content, 'OR:templatename:=' => $content))) {
					$content = $template->getContent();
					$element = $this->modx->newObject('modChunk', array('name' => md5($name)));
					$element->setContent($content);
					$this->addTime('Created chunk from template "'.$template->templatename.'"');
				}
				break;
			case 'CHUNK':
				$name = $content;
				if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
					$content = $element->getContent();
					$this->addTime('Loaded chunk "'.$name.'"');
				}
				break;
			default:
				if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
					$content = $element->getContent();
					$this->addTime('Loaded chunk "'.$name.'"');
				}
		}

		if (!$element) {
			$this->addTime('Could not load or create chunk "'.$name.'".');
			return false;
		}

		// Preparing special tags
		preg_match_all('/\<!--'.$this->config['nestedChunkPrefix'].'(.*?)[\s|\n|\r\n](.*?)-->/s', $content, $matches);
		$src = $dst = $placeholders = array();
		foreach ($matches[1] as $k => $v) {
			$src[] = $matches[0][$k];
			$dst[] = '';
			$placeholders[$v] = $matches[2][$k];
		}
		if (!empty($src) && !empty($dst)) {
			$content = str_replace($src, $dst, $content);
		}

		$chunk = array(
			'object' => $element
			,'content' => $content
			,'placeholders' => $placeholders
		);

		$this->setStore($cache_name, $chunk, 'chunk');
		return $chunk;
	}


	/**
	 * Builds a hierarchical tree from given array
	 *
	 * @param array $tmp Array with rows
	 * @param string $id Name of primary key
	 * @param string $parent Name of parent key
	 *
	 * @return array
	 */
	public function buildTree($tmp = array(), $id = 'id', $parent = 'parent') {
		$rows = $tree= array();
		foreach ($tmp as $v) {
			$rows[$v[$id]] = $v;
		}

		foreach ($rows as $id => &$row) {
			if (empty($row[$parent])) {
				$tree[$id] = &$row;
			}
			else{
				$rows[$row[$parent]]['children'][$id] = &$row;
			}
		}

		ksort($tree);
		return $tree;
	}


	/**
	 * Prepares fetched rows and process template variables
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	public function prepareRows(array $rows = array()) {
		$prepare = $process = array();
		if (!empty($this->config['includeTVs']) && (!empty($this->config['prepareTVs']) || !empty($this->config['processTVs']))) {
			$tvs = array_map('trim', explode(',', $this->config['includeTVs']));
			$prepare = ($this->config['prepareTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['prepareTVs']));
			$process = ($this->config['processTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['processTVs']));
		}

		foreach ($rows as & $row) {
			// Extract JSON fields
			foreach ($row as $k => $v) {
				if (!empty($v) && is_string($v) && ($v[0] == '{' || $v[0] == '[')) {
					$row[$k] = $this->modx->fromJSON($v);
				}
			}

			// Prepare and process TVs
			if (!empty($tvs)) {
				foreach ($tvs as $tv) {
					if (!in_array($tv, $process) && !in_array($tv, $prepare)) {continue;}

					/** @var modTemplateVar $templateVar */
					if (!$templateVar = $this->getStore($tv, 'tv')) {
						if ($templateVar = $this->modx->getObject('modTemplateVar', array('name' => $tv))) {
							$this->setStore($tv, $templateVar, 'tv');
						}
						else {
							$this->addTime('Could not process or prepare TV "'.$tv.'"');
							continue;
						}
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
		}

		if (!empty($tvs)) {
			$this->addTime('Prepared and processed TVs');
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
	public function prepareRow($row = array()) {
		if ($this->preparing) {return $row;}

		// Extract JSON fields
		foreach ($row as $k => $v) {
			if (is_scalar($v) && !empty($v) && ($v[0] == '{' || $v[0] == '[')) {
				$row[$k] = $this->modx->fromJSON($v);
			}
		}

		if (!empty($this->config['prepareSnippet'])) {
			$this->preparing = true;
			$name = trim($this->config['prepareSnippet']);

			/** @var modSnippet $snippet */
			if (!$snippet = $this->getStore($name, 'snippet')) {
				if ($snippet = $this->modx->getObject('modSnippet', array('name' => $name))) {
					$this->setStore($name, $snippet, 'snippet');
				}
				else {
					$this->addTime('Could not load snippet "'.$name.'" for preparation of row.');
					return '';
				}
			}
			$snippet->_cacheable = false;
			$snippet->_processed = false;

			$tmp = $snippet->process(array(
				'pdoTools' => $this,
				'pdoFetch' => $this,
				'row' => $row,
			));

			$tmp = ($tmp[0] == '[' || $tmp[0] == '{')
				? $this->modx->fromJSON($tmp, 1)
				: unserialize($tmp);

			if (!is_array($tmp)) {
				$this->addTime('Preparation snippet must return an array, instead of "'.gettype($tmp).'"');
			}
			else {
				$row = array_merge($row, $tmp);
			}
			$this->preparing = false;
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
	public function checkPermissions($rows = array()) {
		$permissions = array();
		if (!empty($this->config['checkPermissions'])) {
			$tmp = array_map('trim', explode(',', $this->config['checkPermissions']));
			foreach ($tmp as $v) {
				$permissions[$v] = true;
			}
		}
		else {
			return $rows;
		}
		$total = $this->modx->getPlaceholder($this->config['totalVar']);

		foreach ($rows as $key => $row) {
			/** @var modAccessibleObject $object */
			$object = $this->modx->newObject($this->config['class']);
			$object->_fields['id'] = $row['id'];
			if ($object instanceof modAccessibleObject && !$object->checkPolicy($permissions)) {
				unset($rows[$key]);
				$this->addTime($this->config['class'] .' #'.$row['id'].' was excluded from results, because you do not have enough permissions');
				$total--;
			}
		}

		$this->addTime('Checked for permissions "'.implode(',', array_keys($permissions)).'"');
		$this->modx->setPlaceholder($this->config['totalVar'], $total);
		return $rows;
	}

}