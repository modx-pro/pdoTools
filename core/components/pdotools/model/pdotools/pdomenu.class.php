<?php
require_once 'pdofetch.class.php';

class pdoMenu extends pdoFetch {
	/** @var array $tree */
	protected $tree = array();
	/** @var array $parentTree */
	protected $parentTree = array();
	/** @var modResource $currentResource */
	protected $currentResource = null;
	/** @var int $level */
	protected $level = 1;


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX & $modx, $config = array()) {
		$modx->lexicon->load('pdotools:pdomenu');

		$config = array_merge(
			array(
				'firstClass' => 'first',
				'lastClass' => 'last',
				'hereClass' => 'active',
				'parentClass' => '',
				'rowClass' => '',
				'outerClass' => '',
				'innerClass' => '',
				'levelClass' => '',
				'selfClass' => '',
				'webLinkClass' => '',
			),
			$config,
			array(
				'limit' => 0,
				'return' => 'data',
			)
		);

		if (empty($config['tplInner']) && !empty($config['tplOuter'])) {
			$config['tplInner'] = $config['tplOuter'];
		}
		if (empty($config['hereId'])) {
			$config['hereId'] = $modx->resource->id;
		}

		if ($this->currentResource = $modx->getObject('modResource', $config['hereId'])) {
			$tmp = $modx->getParentIds(
				$this->currentResource->id,
				$config['depth'],
				array('context' => $this->currentResource->context_key)
			);
			$tmp[] = $config['hereId'];
			$this->parentTree = array_flip($tmp);
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

		// Flatten tree if needed
		$first = current($tree);
		if (empty($first['id'])) {
			$tmp = $tree;
			$tree = array();
			foreach ($tmp as $v) {
				$tree = array_merge($tree, $v['children']);
			}
		}

		$tplOuter = !empty($this->config['tplOuter'])
			? $this->config['tplOuter']
			: '@INLINE <ul[[+classes]]>[[+wrapper]]</ul>';

		$this->tree = $tree;
		$count = count($tree);

		$idx = 1;
		foreach ($tree as $row) {
			$this->level = 1;
			$row['idx'] = $idx++;
			$row['last'] = (integer) $row['idx'] == $count;

			$output .= $this->templateBranch($row);
		}

		$row = $this->addWayFinderPlaceholders(
			array(
				'wrapper' => $output,
				'classes' => ' class="'.$this->config['outerClass'].'"',
				'classNames' => $this->config['outerClass'],
				'classnames' => $this->config['outerClass'],
			)
		);
		$output = $this->getChunk($tplOuter, $row, $this->config['fastMode']);

		return $output;
	}


	/**
	 * Recursive template of branch of menu
	 *
	 * @param array $row
	 * @param null $tpl
	 *
	 * @return mixed|string
	 */
	public function templateBranch($row = array()) {
		$children = '';
		$row['level'] = $this->level;

		if (!empty($row['children']) && ($this->isHere($row['id']) || empty($this->config['hideSubMenus']))) {
			$idx = 1;
			$this->level++;
			$count = count($row['children']);
			foreach ($row['children'] as $v) {
				$v['idx'] = $idx++;
				$v['last'] = (integer) $v['idx'] == $count;

				$children .= $this->templateBranch($v);
			}
			$this->level--;
			$row['children'] = count($row['children']);
		}
		else {
			$row['children'] = 0;
		}

		if (!empty($children)) {
			$row['wrapper'] = $this->getChunk($this->config['tplInner'], array(
				'wrapper' => $children,
				'classes' => ' class="'.$this->config['innerClass'].'"',
				'classNames' => $this->config['innerClass'],
				'classnames' => $this->config['innerClass'],
			), $this->config['fastMode']);
		}
		else {
			$row['wrapper'] = '';
		}

		if (empty($row['menutitle']) && !empty($row['pagetitle'])) {
			$row['menutitle'] = $row['pagetitle'];
		}

		$classes = $this->getClasses($row);
		if (!empty($classes)) {
			$row['classNames'] = $row['classnames'] = $classes;
			$row['classes'] = ' class="'.$classes.'"';
		}
		else {
			$row['classNames'] = $row['classnames'] = $row['classes'] = '';
		}

		if (!empty($this->config['useWeblinkUrl']) && in_array($row['class_key'], array('modWebLink','modSymLink'))) {
			$row['link'] = is_numeric($row['content'])
				? $this->modx->makeUrl($row['content'], $row['context_key'], '', $this->config['scheme'])
				: $row['content'];
		}
		else {
			$row['link'] = $this->modx->makeUrl($row['id'], $row['context_key'], '', $this->config['scheme']);
		}

		$row['title'] = !empty($this->config['titleOfLinks'])
			? $row[$this->config['titleOfLinks']]
			: '';

		if (empty($row['id'])) {
			$output = $row['wrapper'];
		}
		else {
			$tpl = $this->getTpl($row);
			$row = $this->addWayFinderPlaceholders($row);
			$output = $this->getChunk($tpl, $row, $this->config['fastMode']);
		}

		return $output;
	}


	/**
	 * Determine the "you are here" point in the menu
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function isHere($id = 0) {
		return isset($this->parentTree[$id]);
	}


	/**
	 * Determine style class for current item being processed
	 *
	 * @param array $row Array with resource properties
	 * @param string $type
	 *
	 * @return string
	 */
	public function getClasses($row = array()) {
		$classes = array();

		if (!empty($this->config['rowClass'])) {
			$classes[] = $this->config['rowClass'];
		}
		if ($row['idx'] == 1 && !empty($this->config['firstClass'])) {
			$classes[] = $this->config['firstClass'];
		}
		elseif (!empty($row['last']) && !empty($this->config['lastClass'])) {
			$classes[] = $this->config['lastClass'];
		}
		if (!empty($this->config['levelClass'])) {
			$classes[] = $this->config['levelClass'] . $row['level'];
		}
		if ($row['isfolder'] && !empty($this->config['parentClass']) && ($row['level'] < $this->config['level'] || empty($this->config['level']))) {
			$classes[] = $this->config['parentClass'];
		}
		if ($this->isHere($row['id']) && !empty($this->config['hereClass'])) {
			$classes[] = $this->config['hereClass'];
		}
		if ($row['id'] == $this->config['hereId'] && !empty($this->config['selfClass'])) {
			$classes[] = $this->config['selfClass'];
		}
		if (!empty($row['class_key']) && $row['class_key'] == 'modWebLink' && !empty($this->config['weblinkClass'])) {
			$classes[] = $this->config['weblinkClass'];
		}

		return implode(' ', $classes);
	}


	/**
	 * Determine style class for current item being processed
	 *
	 * @param array $row
	 *
	 * @return mixed
	 */
	public function getTpl($row = array()) {
		if ( $row['level'] == 1 && !empty($this->config['tplStart']) && !empty($this->config['displayStart'])) {
			$tpl = 'tplStart';
		}
		elseif ($row['id'] == $this->config['hereId'] && !empty($this->config['tplParentRowHere']) && $row['isfolder'] && ($row['level'] < $this->config['level'] || empty($this->config['level'])) && !empty($row['children'])) {
			$tpl = 'tplParentRowHere';
		}
		elseif ($row['id'] == $this->config['hereId'] && !empty($this->config['tplInnerHere']) && $row['level'] > 1) {
			$tpl = 'tplInnerHere';
		}
		elseif ($row['id'] == $this->config['hereId'] && !empty($this->config['tplHere'])) {
			$tpl = 'tplHere';
		}
		elseif ($row['isfolder'] && !empty($this->config['tplActiveParentRow']) && ($row['level'] < $this->config['level'] || empty($this->config['level'])) && $this->isHere($row['id'])) {
			$tpl = 'tplActiveParentRow';
		}
		elseif ($row['isfolder'] && !empty($this->config['tplCategoryFolders'])  && (empty($row['template']) || strpos($row['link_attributes'], 'rel="category"') != false) && ($row['level'] < $this->config['level'] || empty($this->config['level']))) {
			$tpl = 'tplCategoryFolders';
		}
		elseif ($row['isfolder'] && !empty($this->config['tplParentRow']) && ($row['level'] < $this->config['level'] || empty($this->config['level'])) && !empty($row['children'])) {
			$tpl = 'tplParentRow';
		}
		elseif ($row['level'] > 1 && !empty($this->config['tplInnerRow'])) {
			$tpl = 'tplInnerRow';
		}
		else {
			$tpl = 'tpl';
		}

		return $this->config[$tpl];
	}


	/**
	 * This method adds special placeholders for compatibility with Wayfinder
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function addWayFinderPlaceholders($row = array()) {
		$pl = $this->config['plPrefix'];
		foreach ($row as $k => $v) {
			switch ($k) {
				case 'id':
					if (!empty($this->config['rowIdPrefix'])) {
						$row[$pl.'id'] = ' id="'.$this->config['rowIdPrefix'].$v.'"';
					}
					$row[$pl.'docid'] = $v;
					break;
				case 'menutitle':
					$row[$pl.'linktext'] = $v;
					break;
				case 'children':
					$row[$pl.'subitemcount'] = $v;
					break;
				default:
					$row[$pl.$k] = $v;
			}
		}

		return $row;
	}

}