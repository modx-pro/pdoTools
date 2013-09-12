<?php

class pdoTools {
	/* @var modX $modx */
	public $modx;
	public $timings = array();
	public $config = array();
	public $elements = array();
	protected $time;
	public $idx = 1;
	protected $count = 0;


	function __construct(modX & $modx) {
		$this->modx = $modx;
		$this->time = microtime(true);
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
	 * Add element to cache
	 *
	 * @return boolean
	 * */
	public function addElement($name, $object) {
		$this->elements[$name] = $object;

		return $this->inCache($name);
	}


	/**
	 * Return element from cache
	 *
	 * @return array|boolean
	 * */
	public function getElement($name) {
		return $this->inCache($name) ? $this->elements[$name] : false;
	}


	/**
	 * Check for existing element
	 *
	 * @return boolean
	 * */
	public function inCache($name) {
		return isset($this->elements[$name]);
	}


	/**
	 * Return quick placeholders from cached element
	 *
	 * @return array
	 */
	public function getPlaceholders($name) {
		return $this->inCache($name) ? $this->elements[$name]['placeholders'] : array();
	}


	/**
	 * Transform array to placeholdres
	 *
	 * @param array $array
	 * @param string $prefix
	 *
	 * @return array
	 */public function makePlaceholders(array $array = array(), $prefix = '') {
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
	 * @param string $chunkName The name of the chunk.
	 * @param array $properties An associative array of properties to process the Chunk with, treated as placeholders within the scope of the Element.
	 * @param boolean $fastMode If true, all MODX tags in chunk will be processed.
	 *
	 * @return string|boolean The processed output of the Chunk.
	 */
	public function getChunk($name = '', array $properties = array(), $fastMode = false) {
		$output = null;

		if (empty($name)) {
			return str_replace(array('[',']','`'), array('&#91;','&#93;','&#96;'), htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'));
		}
		else if (!$this->inCache($name)) {
			/* @var modChunk $element */
			if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
				$element->setCacheable(false);
				$content = $element->getContent();

				// Preparing special tags
				preg_match_all('/\<!--'.$this->config['nestedChunkPrefix'].'(.*?)[\s|\n|\r\n](.*?)-->/s', $content, $matches);
				$src = $dst = $placeholders = array();
				foreach ($matches[1] as $k => $v) {
					$src[] = $matches[0][$k];
					$dst[] = '';
					$placeholders[$v] = $matches[2][$k];
				}
				if (!empty($src) && !empty($dst)) {
					$content = str_replace($src,$dst,$content);
				}

				$chunk = array(
					'object' => $element
					,'content' => $content
					,'placeholders' => $placeholders
				);

				$this->addElement($name, $chunk);
			}
			else {
				return false;
			}
		}
		else {
			$chunk = $this->getElement($name);
		}

		if (!empty($properties) && $chunk['object'] instanceof modChunk) {
			$chunk['object']->_processed = false;
			$chunk['object']->_content = '';

			// Processing quick placeholders
			if ($pl = $this->getPlaceholders($name)) {
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
		}
		else {
			$output = $chunk['content'];
		}

		return $output;
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

		if (!$element = $this->getElement($name)) {
			if ($element = $this->modx->getObject('modSnippet', array('name' => $name))) {
				$this->addElement($name, $element);
			}
		}
		if (!is_object($element) || !($element instanceof modSnippet)) {
			return '';
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
	*/


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
						/*
						case 'inarray': case 'in_array': case 'ia':
							$operand = explode(',', $operand);
							$tplCon = in_array($subject, $operand) ? $conditionalTpl : $tplCon;
							break;
						case 'between': case 'range': case '>=<': case '><':
							$operand = explode(',', $operand);
							$tplCon = ($subject >= min($operand) && $subject <= max($operand)) ? $conditionalTpl : $tplCon;
							break;
						*/
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

}