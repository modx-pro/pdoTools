<?php

if (!class_exists('modParser')) {
	require_once MODX_CORE_PATH . 'model/modx/modparser.class.php';
}

class pdoParser extends modParser {
	/** @var pdoFetch|pdoTools $pdoTools */
	public $pdoTools;

	/**
	 * @param xPDO $modx A reference to the modX|xPDO instance
	 */
	function __construct(xPDO &$modx) {
		parent::__construct($modx);

		/** @var pdoFetch $pdoTools */
		if (!$pdoTools = $modx->getService('pdoFetch')) {
			@session_write_close();
			exit('Fatal error: could not load pdoTools!');
		}
		$this->pdoTools =& $pdoTools;
	}

	/**
	 * Quickly processes a simple tag and returns the result.
	 *
	 * @param string $tag A full tag string parsed from content.
	 * @param boolean $processUncacheable
	 *
	 * @return mixed The output of the processed element represented by the specified tag.
	 */
	public function processTag($tag, $processUncacheable = true) {
		$outerTag = $tag[0];
		$innerTag = $tag[1];
		$processed = false;
		$output = '';

		// Disabled tag
		if ($innerTag[0] == '-') {
			return '';
		}
		// Uncacheable tag
		elseif ($innerTag[0] == '!' && !$processUncacheable) {
			$this->processElementTags($outerTag, $innerTag, $processUncacheable);
			$outerTag = '[['.$innerTag.']]';
			return $outerTag;
		}
		// We processing only certain types of tags without filters and parameters
		elseif (strpos($innerTag, ':') == false && strpos($innerTag, '`') === false && preg_match('/^(?:!|)[-|%|~|+|*|#]+/', $innerTag, $matches)) {
			$innerTag = str_replace($matches[0], '', $innerTag);
			$token = ltrim($matches[0], '!');
			switch ($token) {
				// Lexicon tag
				case '%':
					$tmp = $this->modx->lexicon($innerTag);
					if ($tmp != $innerTag) {
						$output = $tmp;
						$processed = true;
					}
					break;
				// Link tag
				case '~':
					if (is_numeric($innerTag)) {
						if ($tmp = $this->modx->makeUrl($innerTag, '', '', $this->modx->getOption('link_tag_scheme', -1, true))) {
							$output = $tmp;
							$processed = true;
						}
					}
					break;
				// Usual placeholder
				case '+':
					if (isset($this->modx->placeholders[$innerTag])) {
						$output = $this->modx->placeholders[$innerTag];
						$processed = true;
					}
					break;
				// System setting
				case '++':
					if (isset($this->modx->placeholders['+'.$innerTag])) {
						$output = $this->modx->placeholders['+'.$innerTag];
						$processed = true;
					}
					break;
				// Resource tag
				case '*':
					if (is_object($this->modx->resource) && $this->modx->resource instanceof modResource) {
						if ($innerTag == 'content') {
							$output = $this->modx->resource->getContent();
							$processed = true;
						}
						elseif (is_array($this->modx->resource->_fieldMeta) && isset($this->modx->resource->_fieldMeta[$innerTag])) {
							if (isset($this->modx->resource->_fields[$innerTag])) {
								$output = $this->modx->resource->_fields[$innerTag];
							}
							else {
								$output = $this->modx->resource->get($innerTag);
							}
							$processed = true;
						}
					}
					break;
				// FastField tag
				// Thank to Argnist and Dimlight Studio (http://dimlight.ru) for the original idea
				case '#':
					$tmp = array_map('trim', explode('.', $innerTag));
					$length = count($tmp);
					// Resource tag
					if (is_numeric($tmp[0])) {
						/** @var modResource $resource */
						if (!$resource = $this->pdoTools->getStore($tmp[0], 'resource')) {
							$resource = $this->modx->getObject('modResource', $tmp[0]);
							$this->pdoTools->setStore($tmp[0], $resource, 'resource');
						}
						$output = '';
						if (!empty($resource)) {
							// Field specified
							if(!empty($tmp[1])) {
								$tmp[1] = strtolower($tmp[1]);
								if ($tmp[1] == 'content') {
									$output = $resource->getContent();
								}
								// Resource field
								elseif ($field = $resource->get($tmp[1])) {
									$output = $field;
									if (is_array($field)) {
										if ($length > 2) {
											foreach ($tmp as $k => $v) {
												if ($k === 0) {continue;}
												if (isset($field[$v])) {
													$output = $field[$v];
												}
											}
										}
									}
								}
								// Template variable
								elseif ($field === null) {
									$output = $length > 2 && strtolower($tmp[1]) == 'tv'
										? $resource->getTVValue($tmp[2])
										: $resource->getTVValue($tmp[1]);
								}
							}
							// No field specified - print the whole resource
							else {
								$output = $resource->toArray();
							}
						}
					}
					// Global array tag
					else {
						switch (strtolower($tmp[0])) {
							case 'post':	$array = $_POST; break;
							case 'get':		$array = $_GET; break;
							case 'request':	$array = $_REQUEST; break;
							case 'server':	$array = $_SERVER; break;
							case 'files':	$array = $_FILES; break;
							case 'cookie':	$array = $_COOKIE; break;
							case 'session':	$array = $_SESSION; break;
							default: $array = array(); break;
						}
						// Field specified
						if (!empty($tmp[1])) {
							$field = isset($array[$tmp[1]])
								? $array[$tmp[1]]
								: '';
							$output = $field;
							if (is_array($field)) {
								if ($length > 2) {
									foreach ($tmp as $k => $v) {
										if ($k === 0) {continue;}
										if (isset($field[$v])) {
											$output = $field[$v];
										}
									}
								}
							}
						}
						else {
							$output = $array;
						}

						if (is_string($output)) {
							$output = $this->modx->stripTags($output);
						}
					}

					// Additional process of output
					if (is_array($output)) {
						$output = htmlentities(print_r($output, true), ENT_QUOTES, 'UTF-8');
					}
					$processed = true;
					break;
			}
		}

		if ($processed) {
			if ($this->modx->getDebug() === true) {
				$this->modx->log(xPDO::LOG_LEVEL_DEBUG, "Processing {$outerTag} as {$innerTag}:\n" . print_r($output, 1) . "\n\n");
			}
		}
		else {
			$output = parent::processTag($tag, $processUncacheable);
		}

		return $output;
	}

}