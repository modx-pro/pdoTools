<?php

class microMODX {
	public $config;
	public $context = array();
	public $resource = array();
	public $user = array();
	public $lexicon = null;
	public $cacheManager = null;
	protected $pdoTools;
	/** @var modX $modx */
	protected $modx;


	/**
	 * @param pdoTools $pdoTools
	 */
	function __construct(pdoTools $pdoTools) {
		$this->modx = $modx = $pdoTools->modx;
		$this->pdoTools = $pdoTools;
		$this->config = $modx->config;

		if ($modx->context) {
			$this->context = $modx->context->toArray();
		}
		if ($modx->resource) {
			$this->resource = $modx->resource->toArray();
		}
		if ($modx->user) {
			$this->user = $modx->user->toArray();
			if ($profile = $modx->user->getOne('Profile')) {
				$tmp = $profile->toArray();
				unset($tmp['id']);
				$this->user = array_merge($this->user, $tmp);
			}
		}
		$this->lexicon = !empty($modx->lexicon)
			? $modx->lexicon
			: $modx->getService('lexicon', 'modLexicon');
		$this->cacheManager = !empty($modx->cacheManager)
			? $modx->cacheManager
			: $modx->getCacheManager();
	}


	/**
	 * @param $key
	 * @param array $params
	 * @param string $language
	 *
	 * @return null|string
	 */
	public function lexicon($key, $params = array(), $language = '') {
		return $this->modx->lexicon($key, $params, $language);
	}


	/**
	 * @param $chunkName
	 * @param array $properties
	 *
	 * @return string
	 */
	public function getChunk($chunkName, array $properties = array()) {
		if (strpos($chunkName, '!') === 0) {
			$chunkName = substr($chunkName, 1);
		}

		return $this->pdoTools->getChunk($chunkName, $properties);
	}


	/**
	 * @param $chunkName
	 * @param $chunkArr
	 * @param string $prefix
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function parseChunk($chunkName, $chunkArr, $prefix = '[[+', $suffix = ']]') {
		if (strpos($chunkName, '!') === 0) {
			$chunkName = substr($chunkName, 1);
		}

		return $this->pdoTools->parseChunk($chunkName, $chunkArr, $prefix, $suffix);
	}


	/**
	 * @param $snippetName
	 * @param array $params
	 *
	 * @return string
	 */
	public function runSnippet($snippetName, array $params = array()) {
		$output = '';
		$cacheable = true;
		if (strpos($snippetName, '!') === 0) {
			$snippetName = substr($snippetName, 1);
			$cacheable = false;
		}
		if ($this->modx->getParser()) {
			$snippet = $this->modx->parser->getElement('modSnippet', $snippetName);
			if ($snippet instanceof modSnippet) {
				$snippet->setCacheable($cacheable);
				$output = $snippet->process($params);
			}
		}

		return $output;
	}


	/**
	 * @param $id
	 * @param string $context
	 * @param string $args
	 * @param int $scheme
	 * @param array $options
	 *
	 * @return string
	 */
	public function makeUrl($id, $context = '', $args = '', $scheme = -1, array $options = array()) {
		return $this->modx->makeUrl($id, $context, $args, $scheme, $options);
	}


	/**
	 * @param $name
	 * @param $object
	 * @param string $type
	 */
	public function setStore($name, $object, $type = 'data') {
		$this->pdoTools->setStore($name, $object, $type);
	}


	/**
	 * @param $name
	 * @param string $type
	 */
	public function getStore($name, $type = 'data') {
		$this->pdoTools->getStore($name, $type);
	}


	/**
	 * @param $src
	 * @param null $media
	 */
	public function regClientCSS($src, $media = null) {
		$this->modx->regClientCSS($src, $media);
	}


	/**
	 * @param $src
	 * @param bool|false $plaintext
	 */
	public function regClientStartupScript($src, $plaintext = false) {
		$this->modx->regClientStartupScript($src, $plaintext);
	}


	/**
	 * @param $src
	 * @param bool|false $plaintext
	 */
	public function regClientScript($src, $plaintext = false) {
		$this->modx->regClientScript($src, $plaintext);
	}


	/**
	 * @param $html
	 */
	public function regClientStartupHTMLBlock($html) {
		$this->modx->regClientStartupHTMLBlock($html);
	}


	/**
	 * @param $html
	 */
	public function regClientHTMLBlock($html) {
		$this->modx->regClientHTMLBlock($html);
	}


	/**
	 * @param string $action
	 * @param array $scriptProperties
	 * @param array $options
	 */
	public function runProcessor($action = '', $scriptProperties = array(), $options = array()) {
		$this->modx->runProcessor($action, $scriptProperties, $options);
	}


	/**
	 * @param $pm
	 *
	 * @return bool
	 */
	public function hasPermission($pm) {
		return $this->modx->hasPermission($pm);
	}


	/**
	 * @param string $sessionContext
	 *
	 * @return bool
	 */
	public function isAuthenticated($sessionContext = 'web') {
		return $this->modx->user->isAuthenticated($sessionContext);
	}


	/**
	 * @param $context
	 *
	 * @return bool
	 */
	public function hasSessionContext($context) {
		return $this->modx->user->hasSessionContext($context);
	}


	/**
	 * @param $uri
	 * @param string $context
	 *
	 * @return bool|int|mixed
	 */
	public function findResource($uri, $context = '') {
		return $this->modx->findResource($uri, $context);
	}


	/**
	 * @param string $type
	 * @param array $options
	 */
	public function sendError($type = '', $options = array()) {
		$this->modx->sendError($type, $options);
	}


	/**
	 * @param $url
	 * @param bool|false $options
	 * @param string $type
	 * @param string $responseCode
	 */
	public function sendRedirect($url, $options = false, $type = '', $responseCode = '') {
		$this->modx->sendRedirect($url, $options, $type, $responseCode);
	}


	/**
	 * @param $id
	 * @param null $options
	 */
	public function sendForward($id, $options = null) {
		$this->modx->sendForward($id, $options);
	}


	/**
	 * @param $key
	 * @param $value
	 */
	public function setPlaceholder($key, $value) {
		$this->modx->setPlaceholder($key, $value);
	}


	/**
	 * @param $placeholders
	 * @param string $namespace
	 */
	public function setPlaceholders($placeholders, $namespace = '') {
		$this->modx->setPlaceholders($placeholders, $namespace);
	}


	/**
	 * @param $subject
	 * @param string $prefix
	 * @param string $separator
	 * @param bool|false $restore
	 *
	 * @return array
	 */
	public function toPlaceholders($subject, $prefix = '', $separator = '.', $restore = false) {
		return $this->modx->toPlaceholders($subject, $prefix, $separator, $restore);
	}


	/**
	 * @param $key
	 * @param $value
	 * @param string $prefix
	 * @param string $separator
	 * @param bool|false $restore
	 *
	 * @return array
	 */
	public function toPlaceholder($key, $value, $prefix = '', $separator = '.', $restore = false) {
		return $this->modx->toPlaceholder($key, $value, $prefix, $separator, $restore);
	}


	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getPlaceholder($key) {
		return $this->modx->getPlaceholder($key);
	}


	/**
	 * @param $key
	 */
	public function unsetPlaceholder($key) {
		$this->modx->unsetPlaceholder($key);
	}


	/**
	 * @param $keys
	 */
	public function unsetPlaceholders($keys) {
		$this->modx->unsetPlaceholders($keys);
	}
}