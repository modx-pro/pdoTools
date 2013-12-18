<?php

if (!class_exists('modParser')) {
	require_once MODX_CORE_PATH . 'model/modx/modparser.class.php';
}

class pdoParser extends modParser {


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

		// Uncacheable tag
		if ($innerTag[0] == '!' && !$processUncacheable) {
			return $outerTag;
		}
		// Disabled tag
		elseif ($innerTag[0] == '-') {
			return '';
		}
		// We processing only certain types of tags without filters and parameters
		elseif (strpos($innerTag, ':') == false && strpos($innerTag, '`') === false && preg_match('/^(?:!|)[-|%|~|+|*]+/', $innerTag, $matches)) {
			$token = $matches[0];
			$innerTag = str_replace($token, '', $innerTag);
			switch ($token) {
				// Lexicon tag
				case '%':
				case '!%':
					$tmp = $this->modx->lexicon($innerTag);
					if ($tmp != $innerTag) {
						$output = $tmp;
						$processed = true;
					}
					break;
				// Link tag
				case '~':
				case '!~':
					if (is_numeric($innerTag)) {
						if ($tmp = $this->modx->makeUrl($innerTag, '', '', $this->modx->getOption('link_tag_scheme', -1, true))) {
							$output = $tmp;
							$processed = true;
						}
					}
					break;
				// Usual placeholder
				case '+':
				case '!+':
					if (isset($this->modx->placeholders[$innerTag])) {
						$output = $this->modx->placeholders[$innerTag];
						$processed = true;
					}
					break;
				// System setting
				case '++':
				case '!++':
					if (isset($this->modx->placeholders['+'.$innerTag])) {
						$output = $this->modx->placeholders['+'.$innerTag];
						$processed = true;
					}
					break;
				// Resource tag
				case '*':
				case '!*':
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