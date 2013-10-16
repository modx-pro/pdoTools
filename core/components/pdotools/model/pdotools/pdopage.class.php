<?php
require_once 'pdotools.class.php';

class pdoPage extends pdoTools {

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX & $modx, $config = array()) {
		$modx->lexicon->load('pdotools:pdopage');

		return parent::__construct($modx, $config);
	}


	/**
	 * Redirect user to the first page of pagination
	 *
	 * @return string
	 */
	public function redirectToFirst() {
		unset($_GET[$this->config['pageVarKey']]);
		unset($_GET[$this->modx->getOption('request_param_alias', null, 'q')]);
		$this->modx->sendRedirect(
			$this->modx->makeUrl(
				$this->modx->resource->id,
				$this->modx->context->key,
				$_GET,
				'full'
			)
		);
		return '';
	}

	/**
	 * Returns current base url for pagination
	 *
	 * @return string $url
	 */
	public function getBaseUrl() {
		if ($this->modx->getOption('friendly_urls')) {
			$q_var = $this->modx->getOption('request_param_alias', null, 'q');
			$q_val = isset($_GET[$q_var])
				? $_GET[$q_var]
				: '';
			unset($_GET[$q_var]);

			$host = '';
			switch ($this->config['scheme']) {
				case 'full':
					$host = $this->modx->getOption('site_url');
					break;
				case 'abs':
				case 'absolute':
					$host = $this->modx->getOption('base_url');
					break;
				case 'https':
				case 'http':
					$host = $this->config['scheme'] . '://' . $this->modx->getOption('http_host') . $this->modx->getOption('base_url');
					break;
			}
			$url = $host . $q_val;
		}
		else {
			$id_var = $this->modx->getOption('request_param_id', null, 'id');
			$id_val = isset($_GET[$id_var])
				? $_GET[$id_var]
				: $this->modx->getOption('site_start');
			unset($_GET[$id_var]);
			$url = $this->modx->makeUrl($id_val, '', '', $this->config['scheme']);
		}

		return $url;
	}


	/**
	 * Returns templates link for pagination
	 *
	 * @param string $url
	 * @param int $page
	 * @param string $tpl
	 *
	 * @return string $href
	 */
	public function makePageLink($url = '', $page = 1, $tpl = '') {
		if (empty($url)) {
			$url = $this->getBaseUrl();
		}

		$href = $url;
		if ($page > 1) {
			$href .= strpos($href, '?') !== false
				? '&'
				: '?';
			$href .= $this->config['pageVarKey'] . '=' . $page;
		}
		if (!empty($_GET)) {
			if (isset($_GET[$this->config['pageVarKey']])) {
				unset($_GET[$this->config['pageVarKey']]);
			}
			$href .= strpos($href, '?') !== false
				? '&'
				: '?';
			$href .= http_build_query($_GET);
		}

		if (!empty($href) && $this->modx->getOption('xhtml_urls', null, false)) {
			$href = preg_replace("/&(?!amp;)/","&amp;", $href);
		}

		$data = array(
			'page' => $page,
			'pageNo' => $page,
			'href' => $href,
		);

		return !empty($tpl)
			? $this->getChunk($tpl, $data)
			: '';
	}


	/**
	 * Classic pagination: 3,4,5,6,7,8,9,10,11,12,13,14
	 *
	 * @param int $page
	 * @param int $pages
	 * @param string $url
	 *
	 * @return string
	 */
	public function buildClassicPagination($page = 1, $pages = 5, $url = '') {
		$pageLimit = $this->config['pageLimit'];

		if ($pageLimit > $pages) {$pageLimit = 0;}
		else {
			// -1 because we need to show current page
			$tmp = (integer) floor(($pageLimit - 1) / 2);
			$left = $tmp;						// Pages from left
			$right = $pageLimit - $left - 1;	// Pages from right

			if ($page - 1 == 0) {
				$right += $left;
				$left = 0;
			}
			elseif ($page - 1 < $left) {
				$tmp = $left - ($page - 1);
				$left -= $tmp;
				$right += $tmp;
			}
			elseif ($pages - $page == 0) {
				$left += $right;
				$right = 0;
			}
			elseif ($pages - $page < $right) {
				$tmp = $right - ($pages - $page);
				$right -= $tmp;
				$left += $tmp;
			}

			$i = $page - $left;
			$pageLimit = $page + $right;
		}

		if (empty($i)) {$i = 1;}
		$pagination = '';
		while ($i <= $pages) {
			if (!empty($pageLimit) && $i > $pageLimit) {
				break;
			}

			if ($page == $i && !empty($this->config['tplPageActive'])) {
				$tpl = $this->config['tplPageActive'];
			}
			elseif (!empty($this->config['tplPage'])) {
				$tpl = $this->config['tplPage'];
			}

			$pagination .= !empty($tpl)
				? $this->makePageLink($url, $i, $tpl)
				: '';

			$i++;
		}

		return $pagination;
	}


	/**
	 * Modern pagination: 1,2,..,8,9,...,13,14
	 *
	 * @param int $page
	 * @param int $pages
	 * @param string $url
	 *
	 * @return string
	 */
	public function buildModernPagination($page = 1, $pages = 5, $url = '') {
		$pageLimit = $this->config['pageLimit'];

		$left = $right = $center = 0;

		if ($pageLimit >= $pages || $pageLimit < 7) {
			return $this->buildClassicPagination($page, $pages, $url);
		}
		else {
			$tmp = (integer) floor($pageLimit / 3);
			$left = $right = $tmp;
			$center = $pageLimit - ($tmp * 2);
		}

		$pagination = array();
		// Left
		for ($i = 1; $i <= $left; $i++) {
			if ($page == $i && !empty($this->config['tplPageActive'])) {
				$tpl = $this->config['tplPageActive'];
			}
			elseif (!empty($this->config['tplPage'])) {
				$tpl = $this->config['tplPage'];
			}
			$pagination[$i] = !empty($tpl)
				? $this->makePageLink($url, $i, $tpl)
				: '';
		}

		// Right
		for ($i = $pages - $right + 1; $i <= $pages; $i++) {
			if ($page == $i && !empty($this->config['tplPageActive'])) {
				$tpl = $this->config['tplPageActive'];
			}
			elseif (!empty($this->config['tplPage'])) {
				$tpl = $this->config['tplPage'];
			}
			$pagination[$i] = !empty($tpl)
				? $this->makePageLink($url, $i, $tpl)
				: '';
		}

		// Center
		if ($page <= $left) {
			$i = $left + 1;
			while ($i <= $center + $left) {
				if ($i == $center + $left && !empty($this->config['tplPageSkip'])) {
					$tpl = $this->config['tplPageSkip'];
				}
				else {
					$tpl = $this->config['tplPage'];
				}

				$pagination[$i] = !empty($tpl)
					? $this->makePageLink($url, $i, $tpl)
					: '';
				$i++;
			}
		}
		elseif ($page > $pages - $right) {
			$i = $pages - $right - $center + 1;
			while ($i <= $pages - $right) {
				if ($i == $pages - $right - $center + 1 && !empty($this->config['tplPageSkip'])) {
					$tpl = $this->config['tplPageSkip'];
				}
				else {
					$tpl = $this->config['tplPage'];
				}

				$pagination[$i] = !empty($tpl)
					? $this->makePageLink($url, $i, $tpl)
					: '';
				$i++;
			}
		}
		else {
			if ($page - $center < $left) {
				$i = $left + 1;
				while ($i <= $center + $left) {
					if ($page == $i && !empty($this->config['tplPageActive'])) {
						$tpl = $this->config['tplPageActive'];
					}
					elseif (!empty($this->config['tplPage'])) {
						$tpl = $this->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->config['tplPageSkip'])) {
					$key = ($page + 1 == $left + $center)
						? $pages - $right + 1
						: $left + $center;
					$pagination[$key] = $this->getChunk($this->config['tplPageSkip']);
				}
			}
			elseif ($page + $center - 1 > $pages - $right) {
				$i = $pages - $right - $center + 1;
				while ($i <= $pages - $right) {
					if ($page == $i && !empty($this->config['tplPageActive'])) {
						$tpl = $this->config['tplPageActive'];
					}
					elseif (!empty($this->config['tplPage'])) {
						$tpl = $this->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->config['tplPageSkip'])) {
					$key = ($page - 1 == $pages - $right - $center + 1)
						? $left
						: $pages - $right - $center + 1;
					$pagination[$key] = $this->getChunk($this->config['tplPageSkip']);
				}
			}
			else {
				$tmp = (integer) floor(($center - 1) / 2);
				$i = $page - $tmp;
				while ($i < $page - $tmp + $center) {
					if ($page == $i && !empty($this->config['tplPageActive'])) {
						$tpl = $this->config['tplPageActive'];
					}
					elseif (!empty($this->config['tplPage'])) {
						$tpl = $this->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->config['tplPageSkip'])) {
					$pagination[$left] = $pagination[$pages - $right + 1] = $this->getChunk($this->config['tplPageSkip']);
				}
			}
		}

		ksort($pagination);
		return implode($pagination);
	}


	/**
	 * Returns data from cache
	 *
	 * @param int $page
	 *
	 * @return bool|mixed
	 */
	public function getCache($page = 1) {
		$cachePageKey = $this->getCacheKey($page);
		$cacheOptions = $this->getCacheOptions();

		$cached = false;
		if (!empty($cacheOptions) && !empty($cachePageKey) && $this->modx->getCacheManager()) {
			$cached = $this->modx->cacheManager->get($cachePageKey, $cacheOptions);
		}

		return $cached;
	}


	/**
	 * Sets data to cache
	 *
	 * @param int $page
	 * @param array $data
	 *
	 * @return void
	 */
	public function setCache($page = 1, $data = array()) {
		$cachePageKey = $this->getCacheKey($page);
		$cacheOptions = $this->getCacheOptions();

		//$properties['pageUrl'] = $modx->makeUrl($modx->resource->get('id'), '', $qs);
		if (!empty($cachePageKey) && !empty($cacheOptions) && $this->modx->getCacheManager()) {
			$this->modx->cacheManager->set(
				$cachePageKey,
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
	 * This method was originaly written by Agel_Nash for getPageExt
	 *
	 * @param int $page
	 *
	 * @return bool|string
	 */
	public function getCacheKey($page = 1) {
		if (isset($this->config['cache'])) {
			$cache = (!is_scalar($this->config['cache']) || empty($this->config['cache']))
				? false
				: (string) $this->config['cache'];
		} else {
			$cache = (boolean) $this->modx->getOption('cache_resource', null, false);
		}

		if (!$cache) {return false;}

		$cachePagePrefix = !empty($this->config['cachePagePrefix'])
			? $this->config['cachePagePrefix']
			: '';

		switch ($cache) {
			case 'uri':
				$cachePageKey = $this->modx->resource->getCacheKey() . '/' . $cachePagePrefix . $page . '/' . md5($_SERVER['REQUEST_URI']);
				break;

			case 'custom':
				$cachePageKey = !empty($this->config['cachePageKey'])
					? $this->config['cachePageKey']
					: false;
				break;

			case 'modx':
			default:
				$request = $this->modx->request->getParameters(array(), 'REQUEST');
				$cachePageKey = $this->modx->resource->getCacheKey() . '/' . $cachePagePrefix . $page . '/' . md5(http_build_query($request));
		}

		return $cachePageKey;
	}

}