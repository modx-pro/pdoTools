<?php
require_once 'pdofetch.class.php';

class pdoMenu extends pdoFetch {

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX & $modx, $config = array()) {
		$modx->lexicon->load('pdotools:pdomenu');

		$config = array_merge($config, array(
			'limit' => 0,
			'return' => 'data',
		));

		if (empty($config['tplInner']) && !empty($config['tplOuter'])) {
			$config['tplInner'] = $config['tplOuter'];
		}

		return parent::__construct($modx, $config);
	}


	/**
	 * Gets tree of resources and template it
	 *
	 * @param array $tree
	 *
	 * @return mixed
	 */
	public function templateTree($tree = array()) {
		$output = '';

		$tpl = $this->config['tpl'];
		foreach ($tree as $row) {
			$output .= $this->templateBranch($row, $tpl);
		}

		$tplOuter = !empty($this->config['tplOuter'])
			? $this->config['tplOuter']
			: '@INLINE <ul[[+classes]]>[[+wrapper]]</ul>';

		$output = $this->getChunk(
			$tplOuter,
			array('wrapper' => $output, 'classes' => $this->config['outerClass']),
			$this->config['fastMode']
		);
		return $output;
	}


	public function templateBranch($row = array(), $tpl = null) {
		$children = null;

		if (!empty($row['children'])) {
			foreach ($row['children'] as $v) {
				$children .= $this->templateBranch($v, $tpl);
			}
		}

		if (empty($row['menutitle']) && !empty($row['pagetitle'])) {
			$row['menutitle'] = $row['pagetitle'];
		}
		if (!empty($children)) {
			$row['wrapper'] = !empty($this->config['tplInner'])
				? $this->getChunk($this->config['tplInner'], array('wrapper' => $children), $this->config['fastMode'])
				: $this->parseChunk('', $row);
			unset($row['children']);
		}

		if (empty($row['id'])) {
			$output = $row['wrapper'];
		}
		else {
			$output = !empty($tpl)
				? $this->getChunk($tpl, $row, $this->config['fastMode'])
				: $this->parseChunk('', $row);
		}

		return $output;
	}
}