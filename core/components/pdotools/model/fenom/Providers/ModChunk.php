<?php

class modChunkProvider implements \Fenom\ProviderInterface {
	/** @var modX $modx */
	public $modx;


	function __construct(modX $modx) {
		$this->modx = $modx;
	}


	/**
	 * @param string $tpl
	 *
	 * @return bool
	 */
	public function templateExists($tpl) {
		return (bool)$this->modx->getCount('modChunk', array('name' => $tpl));
	}


	/**
	 * @param string $tpl
	 * @param int $time
	 *
	 * @return string
	 */
	public function getSource($tpl, &$time) {
		/** @var modChunk $chunk */
		if ($chunk = $this->modx->getObject('modChunk', array('name' => $tpl))) {
			return $chunk->getContent();
		}

		return '';
	}


	/**
	 * @param string $tpl
	 *
	 * @return int
	 */
	public function getLastModified($tpl) {
		/** @var modChunk $chunk */
		if ($chunk = $this->modx->getObject('modChunk', array('name' => $tpl))) {
			if ($chunk->isStatic() && $file = $chunk->getSourceFile()) {
				return filemtime($file);
			}
		}

		return time();
	}


	/**
	 * Verify templates (check mtime)
	 *
	 * @param array $templates [template_name => modified, ...] By conversation, you may trust the template's name
	 *
	 * @return bool if true - all templates are valid else some templates are invalid
	 */
	public function verify(array $templates) {
		return true;
	}


	/**
	 * Get all names of template from provider
	 * @return array|\Iterator
	 */
	public function getList() {
		$c = $this->modx->newQuery('modChunk');
		$c->select('name');
		if ($c->prepare() && $c->stmt->execute()) {
			return $c->stmt->fetchAll(PDO::FETCH_COLUMN);
		}

		return array();
	}

}