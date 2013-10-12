<?php
require_once 'pdotools.class.php';

class pdoPage extends pdoTools {

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
			$q_val = $_REQUEST[$q_var];
			unset($_REQUEST[$q_var]);

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
			$id_val = $_REQUEST[$id_var];
			unset($_REQUEST[$id_var]);

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
		if (!empty($_REQUEST)) {
			$href .= strpos($href, '?') !== false
				? '&'
				: '?';
			$href .= http_build_query($_REQUEST);
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

}