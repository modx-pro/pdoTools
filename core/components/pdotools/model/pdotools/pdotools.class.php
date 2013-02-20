<?php

class pdoTools {
	/* @var modX $modx */
	var $modx;
	var $timings = array();
	var $config = array();
	private $time;
	public $elements = array();


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


	/**
	 * Process and return the output from a Chunk by name.
	 *
	 * @param string $chunkName The name of the chunk.
	 * @param array $properties An associative array of properties to process
	 * the Chunk with, treated as placeholders within the scope of the Element.
	 * @param boolean $fastMode If true, all MODX tags in chunk will be processed.
	 * @return string The processed output of the Chunk.
	 */
	public function getChunk($name, array $properties = array(), $fastMode = false) {
		$output = null;

		if (!array_key_exists($name, $this->elements)) {
			/* @var modChunk $element */
			if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
				$element->setCacheable(false);
				$content = $element->getContent();

				// processing lexicon placeholders
				preg_match_all('/\[\[%(.*?)\]\]/',$content, $matches);
				$src = $dst = array();
				foreach ($matches[1] as $k => $v) {
					$tmp = $this->modx->lexicon($v);
					if ($tmp != $v) {
						$src[] = $matches[0][$k];
						$dst[] = $tmp;
					}
				}
				$content = str_replace($src,$dst,$content);

				// processing special tags
				preg_match_all('/\<!--'.$this->config['nestedChunkPrefix'].'(.*?)[\s|\n|\r\n](.*?)-->/s', $content, $matches);
				$src = $dst = $placeholders = array();
				foreach ($matches[1] as $k => $v) {
					$src[] = $matches[0][$k];
					$dst[] = '';
					$placeholders[$v] = $matches[2][$k];
				}
				$content = str_replace($src,$dst,$content);

				$chunk = array(
					'object' => $element
					,'content' => $content
					,'placeholders' => $placeholders
				);

				$this->elements[$name] = $chunk;
			}
			else {
				return false;
			}
		}
		else {
			$chunk = $this->elements[$name];
		}

		$chunk['object']->_processed = false;
		$chunk['object']->_content = '';

		if (!empty($properties) && $chunk['object'] instanceof modChunk) {
			$pl = $this->makePlaceholders($properties);
			$content = str_replace($pl['pl'], $pl['vl'], $chunk['content']);
			$content = str_replace($pl['pl'], $pl['vl'], $content);
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