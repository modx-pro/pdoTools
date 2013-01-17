<?php

class pdoTools {
	/* @var modX $modx */
	var $modx;
	var $timings = array();
	var $config = array();
	private $time;
	private $elements = array();


	function __construct(modX & $modx) {
		$this->modx = $modx;
		$this->time = microtime(true);
	}


	public function addTime($message) {
		$time = microtime(true);
		$this->timings[] = array(
			'time' =>  number_format(round(($time - $this->time), 7), 7)
			,'message' => $message
		);
		$this->time = $time;
	}


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

			$res .= number_format(round($sum, 7), 7) . ': <b>Total time</b>';
			return $res;
		}
	}


	public function makePlaceholders(array $array) {
		$placeholders = array('pl' => array(),'vl' => array());
		foreach ($array as $k => $v) {
			$placeholders['pl'][] = "[[+$k]]";
			$placeholders['vl'][] = $v;
		}
		return $placeholders;
	}


	public function getChunk($name, array $properties = array(), $fastMode = false) {
		$output = null;

		if (!array_key_exists($name, $this->elements)) {
			/* @var modChunk $element */
			if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
				$element->setCacheable(false);
				$chunk = array(
					'object' => $element
					,'content' => $element->getContent()
				);
				$this->elements[$name] = $chunk;
			}
			else {
				return false;
			}
		}
		else {
			$chunk = $this->elements[$name];
			$chunk['object']->_processed = false;
		}

		if (!empty($properties) && $chunk['object'] instanceof modChunk) {
			$pl = $this->makePlaceholders($properties);
			$content = str_replace($pl['pl'], $pl['vl'], $chunk['content']);
			if ($fastMode) {
				$matches = $tags = array();
				$this->modx->parser->collectElementTags($content, $matches);
				foreach ($matches as $v) {
					$tags[] = $v[0];
				}
				$output = str_replace($tags, '', $content);
			}
			else {
				$output = $chunk['object']->process($properties, $content);
			}
		}
		else {
			$output = $chunk['content'];
		}

		return $output;
	}
}