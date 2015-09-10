<?php

class pdoPage {
	/** @var modX $modx */
	public $modx;
	/** @var pdoTools $pdoTools */
	public $pdoTools;
	/** @var string $req_var */
	protected $req_var = '';


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	public function __construct(modX & $modx, $config = array()) {
		$this->modx = &$modx;

		$fqn = $modx->getOption('pdoTools.class', null, 'pdotools.pdotools', true);
		if ($pdoClass = $modx->loadClass($fqn, '', false, true)) {
			$this->pdoTools = new $pdoClass($modx, $config);
		}
		elseif ($pdoClass = $modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
			$this->pdoTools = new $pdoClass($modx, $config);
		}
		else {
			$this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoTools from "MODX_CORE_PATH/components/pdotools/model/".');
			return false;
		}

		$modx->lexicon->load('pdotools:pdopage');
		return true;
	}


	/**
	 * Loads javascript and styles for ajax pagination
	 *
	 */
	public function loadJsCss() {
		$assetsUrl = !empty($this->pdoTools->config['assetsUrl'])
			? $this->pdoTools->config['assetsUrl']
			: MODX_ASSETS_URL . 'components/pdotools/';
		if ($css = trim($this->pdoTools->config['frontend_css'])) {
			$this->modx->regClientCSS(str_replace('[[+assetsUrl]]', $assetsUrl, $css));
		}
		if ($js = trim($this->pdoTools->config['frontend_js'])) {
			$this->modx->regClientScript(str_replace('[[+assetsUrl]]', $assetsUrl, $js));
		}
		$limit = $this->pdoTools->config['limit'] > $this->pdoTools->config['maxLimit']
			? $this->pdoTools->config['maxLimit']
			:  $this->pdoTools->config['limit'];
		$moreChunk = $this->modx->getOption('ajaxTplMore', $this->pdoTools->config, '@INLINE <button class="btn btn-default btn-more">[[%pdopage_more]]</button>');
		$moreTpl = str_replace('"', '\"', $this->pdoTools->getChunk($moreChunk, array('limit' => $limit)));

		$config = '{
			wrapper: "' . $this->modx->getOption('ajaxElemWrapper', $this->pdoTools->config, '#pdopage') . '",
			rows: "' . $this->modx->getOption('ajaxElemRows', $this->pdoTools->config, '#pdopage .rows') . '",
			pagination: "' . $this->modx->getOption('ajaxElemPagination', $this->pdoTools->config, '#pdopage .pagination') . '",
			link: "' . $this->modx->getOption('ajaxElemLink', $this->pdoTools->config, '#pdopage .pagination a') . '",
			more: "' . $this->modx->getOption('ajaxElemMore', $this->pdoTools->config, '#pdopage .btn-more') . '",
			moreTpl: "' . $moreTpl . '",
			mode: "' . $this->pdoTools->config['ajaxMode'] . '",
			pageVarKey: "' . $this->pdoTools->config['pageVarKey'] . '",
			pageLimit: ' . $limit . ',
			assetsUrl: "' . $assetsUrl . '"
		}';
		$this->modx->regClientStartupScript('<script type="text/javascript">pdoPage = {callbacks: {}, keys: {}};</script>', true);
		$this->modx->regClientScript('<script type="text/javascript">pdoPage.initialize(' . $config . ');</script>', true);
	}


	/**
	 * Redirect user to the first page of pagination
	 *
	 * @param $isAjax
	 *
	 * @return string
	 */
	public function redirectToFirst($isAjax = false) {
		unset($_GET[$this->pdoTools->config['pageVarKey']]);
		unset($_GET[$this->modx->getOption('request_param_alias', null, 'q')]);
		if (!$isAjax) {
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
		else {
			$_GET[$this->pdoTools->config['pageVarKey']] = 1;
			$_REQUEST = $_GET;
			return $this->modx->runSnippet('pdoPage', $this->pdoTools->config);
		}
	}


	/**
	 * Returns current base url for pagination
	 *
	 * @return string $url
	 */
	public function getBaseUrl() {
		if ($this->modx->getOption('friendly_urls')) {
			$q_var = $this->modx->getOption('request_param_alias', null, 'q');
			$q_val = isset($_REQUEST[$q_var])
				? $_REQUEST[$q_var]
				: '';
			$this->req_var = $q_var;

			$host = '';
			switch ($this->pdoTools->config['scheme']) {
				case 'full':
					$host = $this->modx->getOption('site_url');
					break;
				case 'abs':
				case 'absolute':
					$host = $this->modx->getOption('base_url');
					break;
				case 'https':
				case 'http':
					$host = $this->pdoTools->config['scheme'] . '://' . $this->modx->getOption('http_host') . $this->modx->getOption('base_url');
					break;
			}
			$url = $host . $q_val;
		}
		else {
			$id_var = $this->modx->getOption('request_param_id', null, 'id');
			$id_val = isset($_GET[$id_var])
				? $_GET[$id_var]
				: $this->modx->getOption('site_start');
			$this->req_var = $id_var;

			$url = $this->modx->makeUrl($id_val, '', '', $this->pdoTools->config['scheme']);
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
		if ($page > 1 || ($page == 1 && !empty($this->pdoTools->config['ajax']))) {
			$href .= strpos($href, '?') !== false
				? '&'
				: '?';
			$href .= $this->pdoTools->config['pageVarKey'] . '=' . $page;
		}
		if (!empty($_GET)) {
			$request = $_GET;
			unset($request[$this->req_var]);
			unset($request[$this->pdoTools->config['pageVarKey']]);

			if (!empty($request)) {
				$href .= strpos($href, '?') !== false
					? '&'
					: '?';
				$href .= http_build_query($request);
			}
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
			? $this->pdoTools->getChunk($tpl, $data)
			: $href;
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
		$pageLimit = $this->pdoTools->config['pageLimit'];

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

			if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
				$tpl = $this->pdoTools->config['tplPageActive'];
			}
			elseif (!empty($this->pdoTools->config['tplPage'])) {
				$tpl = $this->pdoTools->config['tplPage'];
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
		$pageLimit = $this->pdoTools->config['pageLimit'];

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
			if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
				$tpl = $this->pdoTools->config['tplPageActive'];
			}
			elseif (!empty($this->pdoTools->config['tplPage'])) {
				$tpl = $this->pdoTools->config['tplPage'];
			}
			$pagination[$i] = !empty($tpl)
				? $this->makePageLink($url, $i, $tpl)
				: '';
		}

		// Right
		for ($i = $pages - $right + 1; $i <= $pages; $i++) {
			if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
				$tpl = $this->pdoTools->config['tplPageActive'];
			}
			elseif (!empty($this->pdoTools->config['tplPage'])) {
				$tpl = $this->pdoTools->config['tplPage'];
			}
			$pagination[$i] = !empty($tpl)
				? $this->makePageLink($url, $i, $tpl)
				: '';
		}

		// Center
		if ($page <= $left) {
			$i = $left + 1;
			while ($i <= $center + $left) {
				if ($i == $center + $left && !empty($this->pdoTools->config['tplPageSkip'])) {
					$tpl = $this->pdoTools->config['tplPageSkip'];
				}
				else {
					$tpl = $this->pdoTools->config['tplPage'];
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
				if ($i == $pages - $right - $center + 1 && !empty($this->pdoTools->config['tplPageSkip'])) {
					$tpl = $this->pdoTools->config['tplPageSkip'];
				}
				else {
					$tpl = $this->pdoTools->config['tplPage'];
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
					if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
						$tpl = $this->pdoTools->config['tplPageActive'];
					}
					elseif (!empty($this->pdoTools->config['tplPage'])) {
						$tpl = $this->pdoTools->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->pdoTools->config['tplPageSkip'])) {
					$key = ($page + 1 == $left + $center)
						? $pages - $right + 1
						: $left + $center;
					$pagination[$key] = $this->pdoTools->getChunk($this->pdoTools->config['tplPageSkip']);
				}
			}
			elseif ($page + $center - 1 > $pages - $right) {
				$i = $pages - $right - $center + 1;
				while ($i <= $pages - $right) {
					if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
						$tpl = $this->pdoTools->config['tplPageActive'];
					}
					elseif (!empty($this->pdoTools->config['tplPage'])) {
						$tpl = $this->pdoTools->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->pdoTools->config['tplPageSkip'])) {
					$key = ($page - 1 == $pages - $right - $center + 1)
						? $left
						: $pages - $right - $center + 1;
					$pagination[$key] = $this->pdoTools->getChunk($this->pdoTools->config['tplPageSkip']);
				}
			}
			else {
				$tmp = (integer) floor(($center - 1) / 2);
				$i = $page - $tmp;
				while ($i < $page - $tmp + $center) {
					if ($page == $i && !empty($this->pdoTools->config['tplPageActive'])) {
						$tpl = $this->pdoTools->config['tplPageActive'];
					}
					elseif (!empty($this->pdoTools->config['tplPage'])) {
						$tpl = $this->pdoTools->config['tplPage'];
					}
					$pagination[$i] = !empty($tpl)
						? $this->makePageLink($url, $i, $tpl)
						: '';
					$i++;
				}
				if (!empty($this->pdoTools->config['tplPageSkip'])) {
					$pagination[$left] = $pagination[$pages - $right + 1] = $this->pdoTools->getChunk($this->pdoTools->config['tplPageSkip']);
				}
			}
		}

		ksort($pagination);
		return implode($pagination);
	}

}