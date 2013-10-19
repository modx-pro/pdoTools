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
				100,
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
		$this->tree = $tree;
		$count = count($tree);
		$output = '';

		$idx = 1;
		$this->addTime('Start template tree');
		foreach ($tree as $row) {
			if (empty($row['id'])) {continue;}
			$this->level = 1;
			$row['idx'] = $idx++;
			$row['last'] = (integer) $row['idx'] == $count;

			$output .= $this->templateBranch($row);
		}
		$this->addTime('End template tree');

		$row = $this->addWayFinderPlaceholders(
			array(
				'wrapper' => $output,
				'classes' => ' class="'.$this->config['outerClass'].'"',
				'classNames' => $this->config['outerClass'],
				'classnames' => $this->config['outerClass'],
			)
		);

		return $this->parseChunk($this->config['tplOuter'], $row);
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

		if (!empty($row['children']) && ($this->isHere($row['id']) || empty($this->config['hideSubMenus'])) && $this->checkResource($row['id'])) {
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
			$pls = $this->addWayFinderPlaceholders(array(
				'wrapper' => $children,
				'classes' => ' class="'.$this->config['innerClass'].'"',
				'classNames' => $this->config['innerClass'],
				'classnames' => $this->config['innerClass'],
			));
			$row['wrapper'] = $this->parseChunk($this->config['tplInner'], $pls);
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

		$tpl = $this->getTpl($row);
		$row = $this->addWayFinderPlaceholders($row);

		return $this->getChunk($tpl, $row, $this->config['fastMode']);
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
				case 'link_attributes':
					$row[$pl.'attributes'] = $v;
					$row['attributes'] = $v;
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


	/**
	 * Verification of resource status
	 *
	 * @param int $id
	 *
	 * @return bool|int
	 */
	public function checkResource($id = 0) {
		$tmp = array();
		if (empty($this->config['showHidden'])) {
			$tmp['hidemenu'] = 0;
		}
		if (empty($this->config['showUnpublished'])) {
			$tmp['published'] = 1;
		}
		if (!empty($this->config['hideUnsearchable'])) {
			$tmp['searchable'] = 1;
		}

		if (!empty($tmp)) {
			$tmp['id'] = $id;
			$q = $this->modx->newQuery('modResource', $tmp);
			$q->select('id');

			$tstart = microtime(true);
			if ($q->prepare() && $q->stmt->execute()) {
				$this->modx->queryTime += microtime(true) - $tstart;
				$this->modx->executedQueries++;
				$res = $q->stmt->fetch(PDO::FETCH_COLUMN);
				return (boolean) $res;
			}
		}

		return true;
	}


	/**
	 * Returns data from cache
	 *
	 * @var mixed $key
	 *
	 * @return bool|mixed
	 */
	public function getCache($key = '') {
		$cacheKey = $this->getCacheKey($key);
		$cacheOptions = $this->getCacheOptions();

		$cached = false;
		if (!empty($cacheOptions) && !empty($cacheKey) && $this->modx->getCacheManager()) {
			$cached = $this->modx->cacheManager->get($cacheKey, $cacheOptions);
		}

		return $cached;
	}


	/**
	 * Sets data to cache
	 *
	 * @param array $data
	 * @var mixed $key
	 *
	 * @return void
	 */
	public function setCache($data = array(), $key = '') {
		$cacheKey = $this->getCacheKey($key);
		$cacheOptions = $this->getCacheOptions();

		if (!empty($cacheKey) && !empty($cacheOptions) && $this->modx->getCacheManager()) {
			$this->modx->cacheManager->set(
				$cacheKey,
				$data,
				$cacheOptions[xPDO::OPT_CACHE_EXPIRES],
				$cacheOptions
			);
		}
	}


	/**
	 * Returns array with options for cache
	 *
	 * @return array
	 */
	public function getCacheOptions() {
		$cacheOptions = array(
			xPDO::OPT_CACHE_KEY => !empty($this->config['cache_key'])
				? $this->config['cache_key']
				: $this->modx->getOption('cache_resource_key', null, 'resource'),
			xPDO::OPT_CACHE_HANDLER => !empty($this->config['cache_handler'])
				? $this->config['cache_handler']
				: $this->modx->getOption('cache_resource_handler', null, 'xPDOFileCache'),
			xPDO::OPT_CACHE_EXPIRES => $this->config['cacheTime'] !== ''
				? (integer) $this->config['cacheTime']
				: (integer) $this-> modx->getOption('cache_resource_expires', null, 0),
		);

		return $cacheOptions;
	}


	/**
	 * Returns key for cache
	 *
	 * @var mixed $key
	 *
	 * @return bool|string
	 */
	public function getCacheKey($key = '') {
		if (isset($this->config['cache'])) {
			$cache = (!is_scalar($this->config['cache']) || empty($this->config['cache']))
				? false
				: (string) $this->config['cache'];
		} else {
			$cache = (boolean) $this->modx->getOption('cache_resource', null, false);
		}

		if (!$cache) {return false;}

		$cachePrefix = !empty($this->config['cachePrefix'])
			? $this->config['cachePrefix']
			: '';
		if (empty($key)) {$key = $this->config;}

		$cacheKey = $this->modx->resource->getCacheKey() . '/' . $cachePrefix . $this->modx->user->id . '-' . md5(base64_encode(serialize($key)));

		return $cacheKey;
	}

}