<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Request
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;
use YapepBase\UtilityFunctions;

/**
 * Request class for HTTP requests.
 *
 * Stores the details for the current request.
 *
 * @package    YapepBase
 * @subpackage Request
 * @todo $_FILES handling
 */
class HttpRequest implements IRequest {

	/**
	 * The GET parameters received with the request.
	 *
	 * @var array
	 */
	protected $getParams;

	/**
	 * The POST parameters received with the request.
	 *
	 * @var array
	 */
	protected $postParams;

	/**
	 * The cookies received with the request.
	 *
	 * @var array
	 */
	protected $cookies;

	/**
	 * The information of the files uploaded with the request.
	 *
	 * @var array
	 */
	protected $files;

	/**
	 * The server array.
	 *
	 * @var array
	 */
	protected $server;

	/**
	 * The environment array.
	 *
	 * @var array
	 */
	protected $env;

	/**
	 * The target URI
	 *
	 * @var string
	 */
	protected $targetUri;

	/**
	 * The route params
	 *
	 * @var array
	 */
	protected $routeParams = array();

	/**
	 * Caches the protected content types
	 *
	 * @var array
	 */
	protected $acceptedContentTypes;

	/**
	 * Constructor.
	 *
	 * @param array $get                  The $_GET array
	 * @param array $post                 The $_POST array
	 * @param array $cookie               The $_COOKIE array
	 * @param array $server               The $_SERVER array
	 * @param array $env                  The $_ENV array
	 * @param array $files                The $_FILES array
	 * @param bool  $magicQuotesEnabled   If TRUE, the get, post, cookie arrays will be recursively stripped of slashes.
	 */
	public function __construct(
		array $get, array $post, array $cookie, array $server, array $env, array $files, $magicQuotesEnabled = null
	) {
		if (is_null($magicQuotesEnabled)) {
			$magicQuotesEnabled = \get_magic_quotes_gpc();
		}

		if ($magicQuotesEnabled) {
			$get = UtilityFunctions::recursiveStripSlashes($get);
			$post = UtilityFunctions::recursiveStripSlashes($post);
			$cookie = UtilityFunctions::recursiveStripSlashes($cookie);
		}

		$this->getParams = $get;
		$this->postParams = $post;
		$this->cookies = $cookie;
		$this->server = $server;
		$this->env = $env;
		$this->files = $files;

		list($this->targetUri) = explode('?', $this->server['REQUEST_URI'], 2);
	}

	/**
	 * Returns the GET parameter specified, or the default value, if it's not set.
	 *
	 * @param string $name      The name of the parameter.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 *
	 * @return mixed
	 */
	public function getGet($name, $default = null) {
		if (isset($this->getParams[$name])) {
			return $this->getParams[$name];
		}
		return $default;
	}

	/**
	 * Returns TRUE if there is a GET parameter set in the request with the specified name.
	 *
	 * @param string $name   The name of the get param.
	 *
	 * @return bool
	 */
	public function hasGet($name) {
		return $this->has($name, 'G');
	}

	/**
	 * Returns the POST parameter specified, or the default value, if it's not set.
	 *
	 * @param string $name      The name of the parameter.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 *
	 * @return mixed
	 */
	public function getPost($name, $default = null) {
		if (isset($this->postParams[$name])) {
			return $this->postParams[$name];
		}
		return $default;
	}

	/**
	 * Returns TRUE if there is a POST parameter set in the request with the specified name.
	 *
	 * @param string $name   Name of the post param.
	 *
	 * @return bool
	 */
	public function hasPost($name) {
		return $this->has($name, 'P');
	}

	/**
	 * Returns the specified cookie, or the default value, if it's not set.
	 *
	 * @param string $name      The name of the cookie.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 *
	 * @return mixed
	 */
	public function getCookie($name, $default = null) {
		if (isset($this->cookies[$name])) {
			return $this->cookies[$name];
		}
		return $default;
	}

	/**
	 * Returns TRUE if there is a cookie set in the request with the specified name.
	 *
	 * @param string $name   name of the cookie
	 *
	 * @return bool
	 */
	public function hasCookie($name) {
		return $this->has($name, 'C');
	}

	/**
	 * Returns all of the parameters received through URI.
	 *
	 * @return array
	 */
	public function getAllUri() {
		return $this->routeParams;
	}


	/**
	 * Returns all of the parameters received through GET.
	 *
	 * @return array
	 */
	public function getAllGet() {
		return $this->getParams;
	}

	/**
	 * Returns all of the parameters received through POST.
	 *
	 * @return array
	 */
	public function getAllPost() {
		return $this->postParams;
	}

	/**
	 * Returns all of the parameters received through COOKIE.
	 *
	 * @return array
	 */
	public function getAllCookie() {
		return $this->cookies;
	}

	/**
	 * Retruns the specified route param, or the default value if it's not set.
	 *
	 * @param string $name      The name of the cookie.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 *
	 * @return mixed
	 */
	public function getParam($name, $default = null) {
		if (isset($this->routeParams[$name])) {
			return $this->routeParams[$name];
		}
		return $default;
	}

	/**
	 * Returns a value from the PHP server array.
	 *
	 * @param string $name      The key of the value to return.
	 * @param mixed  $default   The default value, if the key is not set.
	 *
	 * @return mixed   The value, or the provided default, if the key is not found.
	 */
	public function getServer($name, $default = null) {
		if (isset($this->server[$name])) {
			return $this->server[$name];
		}
		return $default;
	}

	/**
	 * Returns a value from the running environment.
	 *
	 * @param string $name      The key of the value to return.
	 * @param mixed  $default   The default value, if the key is not set.
	 *
	 * @return mixed   The value, or the provided default, if the key is not found.
	 */
	public function getEnv($name, $default = null) {
		if (isset($this->env[$name])) {
			return $this->env[$name];
		}
		return $default;
	}

	/**
	 * Returns the specified request parameter from the specified source, or the default value.
	 *
	 * Search order is UGPC, so a POST value will overwrite a GET value with the same name.
	 *
	 * @param string $name      The name of the param.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 * @param string $source    The sources of the parameter. 'U' for URI, 'G' for GET, 'P' for POST, 'C' for Cookie.
	 *
	 * @return mixed
	 */
	public function get($name, $default = null, $source = 'UGP') {
		$source = strtoupper($source);
		$result = $default;

		if (strstr($source, 'U') && isset($this->routeParams[$name])) {
			$result = $this->routeParams[$name];
		}

		if (strstr($source, 'G') && isset($this->getParams[$name])) {
			$result = $this->getParams[$name];
		}

		if (strstr($source, 'P') && isset($this->postParams[$name])) {
			$result = $this->postParams[$name];
		}

		if (strstr($source, 'C') && isset($this->cookies[$name])) {
			$result = $this->cookies[$name];
		}

		return $result;
	}

	/**
	 * Returns TRUE if the specified request parameter from the specified source is set.
	 *
	 * @param string $name     The name of the param.
	 * @param string $source   The sources of the parameter. 'U' for URI, 'G' for GET, 'P' for POST, 'C' for Cookie.
	 *
	 * @return bool
	 */
	public function has($name, $source = 'UGP') {
		$source = strtoupper($source);

		if (
			(strstr($source, 'U') && isset($this->routeParams[$name]))
			|| (strstr($source, 'G') && isset($this->getParams[$name]))
			|| (strstr($source, 'P') && isset($this->postParams[$name]))
			|| (strstr($source, 'C') && isset($this->cookies[$name]))
		) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the target of the request.  (eg the URI for HTTP requests)
	 *
	 * @return string   The target of the request.
	 */
	public function getTarget() {
		return $this->targetUri;
	}

	/**
	 * Returns the method of the request
	 *
	 * @return string   {@uses self::METHOD_*}
	 */
	public function getMethod() {
		return $this->server['REQUEST_METHOD'];
	}

	/**
	 * Sets a route param
	 *
	 * @param string $name    Name of the param.
	 * @param mixed  $value   Value of the param.
	 *
	 * @return void
	 */
	public function setParam($name, $value) {
		$this->routeParams[$name] = $value;
	}

	/**
	 * Returns TRUE if the request was made as an AJAX request.
	 *
	 * @return bool
	 */
	public function isAjaxRequest() {
		return (!empty($this->server['HTTP_X_REQUESTED_WITH'])
			&& 'xmlhttprequest' == strtolower($this->server['HTTP_X_REQUESTED_WITH']));
	}

	/**
	 * Parses the accept setHeader and returns an array with all the parsed accepted content types.
	 *
	 * The returned array has the following keys:
	 * <ul>
	 *   <li>mimeType: The full MIME type</li>
	 *   <li>type:     The type part of the MIME type</li>
	 *   <li>subType:  The subtype part of the MIME type</li>
	 *   <li>params:   An associative array with all the params. The key is the name of the param, the value is
	 *                 it's value.</li>
	 *   <li>original: The original, unparsed content type item</li>
	 * </ul>
	 *
	 * @return array   The parsed array
	 */
	public function getAcceptedContentTypes() {
		if (is_array($this->acceptedContentTypes)) {
			return $this->acceptedContentTypes;
		}
		$header = trim($this->getServer('HTTP_ACCEPT', ''));
		if (empty($header)) {
			return array();
		}

		$types = explode(',', $header);
		$this->acceptedContentTypes = array();
		foreach ($types as $type) {
			$parsed = $this->parseContentType($type);
			if (false !== $parsed) {
				$this->acceptedContentTypes[] = $parsed;
			}
		}

		return $this->acceptedContentTypes;
	}

	/**
	 * Parses a content type string and returns an associative array with the parsed values
	 *
	 * The returned array has the following keys:
	 * <ul>
	 *   <li>mimeType: The full MIME type</li>
	 *   <li>type:     The type part of the MIME type</li>
	 *   <li>subType:  The subtype part of the MIME type</li>
	 *   <li>params:   An associative array with all the params. The key is the name of the param, the value is
	 *                 it's value.</li>
	 *   <li>original: The original, unparsed content type item</li>
	 * </ul>
	 *
	 * @param string $type   The content type {@uses \YapepBase\Mime\MimeType::*}
	 *
	 * @return array|bool   The array with the values or FALSE if it is invalid
	 */
	protected function parseContentType($type) {
		$parts = explode(';', trim($type));
		$mime = trim(array_shift($parts));
		$mimeParts = explode('/', $mime, 2);

		if (empty($mime) || 2 != count($mimeParts) || empty($mimeParts[0]) || empty($mimeParts[1])) {
			return false;
		}

		$params = array();

		foreach ($parts as $part) {
			$paramParts = explode('=', trim($part), 2);
			if (2 != count($paramParts)) {
				continue;
			}
			$params[trim($paramParts[0])] = trim($paramParts[1]);
		}

		if (isset($params['q'])) {
			$params['q'] = (float)$params['q'];
		} else {
			$params['q'] = 1.0;
		}

		return array(
			'mimeType' => $mime,
			'type'     => trim($mimeParts[0]),
			'subType'  => trim($mimeParts[1]),
			'params'   => $params,
			'original' => trim($type),
		);
	}

	/**
	 * Returns the accepted content types in the order of preference by the client.
	 *
	 * The returned values have the same structure as the HttpRequest::getAcceptedContentTypes method.
	 *
	 * @return array
	 *
	 * @see HttpRequest::getAcceptedContentTypes()
	 */
	public function getAcceptedContentTypesByPreference() {
		$sortFunction = function($a, $b) {
			// Compare by preference
			if ($a['params']['q'] != $b['params']['q']) {
				return ($a['params']['q'] < $b['params']['q'] ? -1 : 1);
			}

			// Compare by type specificity
			if ($a['type'] == '*' || $b['type'] == '*') {
				if ($a['type'] == '*') {
					if ($b['type'] == '*') {
						return 0;
					}
					return -1;
				}
				// A is not *, B is, A is greater
				return 1;
			}

			if ($a['subType'] == '*' || $b['subType'] == '*') {
				if ($a['subType'] == '*') {
					if ($b['subType'] == '*') {
						return 0;
					}
					return -1;
				}
				return 1;
			}

			if (count($a['params']) == count($b['params'])) {
				return 0;
			}

			return (count($a['params']) < count($b['params']) ? -1 : 1);
		};

		$acceptedTypes = $this->getAcceptedContentTypes();

		usort($acceptedTypes, $sortFunction);

		return array_reverse($acceptedTypes);
	}

	/**
	 * Returns TRUE if the checked content type matches the accepted content type.
	 *
	 * The provided parameters must have the structure of the return value of the HttpRequest::parseContentType()
	 * method.
	 *
	 * @param array $checkedContentType   The content type to check.
	 * @param array $acceptedType         The content type to check against. May contain wildcards.
	 * @param int   $specificity          The level of specificity (outgoing parameter).
	 *
	 * @return bool
	 *
	 * @see HttpRequest::parseContentType()
	 */
	protected function checkIfContentTypesMatch(array $checkedContentType, array $acceptedType, &$specificity = null) {
		$specificity = 0;

		if ($acceptedType['type'] == '*') {
			return true;
		}

		if ($checkedContentType['type'] == $acceptedType['type'] && $acceptedType['subType'] == '*') {
			$specificity = 1;
			return true;
		}

		unset($acceptedType['params']['q']);

		if (
			$checkedContentType['type'] == $acceptedType['type']
			&& $checkedContentType['subType'] == $acceptedType['subType']
		) {
			if (empty($acceptedType['params'])) {
				$specificity = 2;
				return true;
			}
			foreach ($acceptedType['params'] as $key => $value) {
				if (!isset($checkedContentType['params'][$key]) || $checkedContentType['params'][$key] != $value) {
					return false;
				}
			}
			$specificity = count($acceptedType['params']) + 2;
			return true;
		}

		return false;
	}

	/**
	 * Returns TRUE if the provided content type is one of the content types accepted by the client, FALSE otherwise.
	 *
	 * IF there was no Accept setHeader in the request, it will always return TRUE for a valid content type.
	 *
	 * @param string $contentType   The content type {@uses \YapepBase\Mime\MimeType::*}
	 *
	 * @return bool
	 */
	public function checkIfContentTypeIsPreferred($contentType) {
		$parsed = $this->parseContentType($contentType);
		if (false === $parsed) {
			return false;
		}

		$acceptedTypes = $this->getAcceptedContentTypes();

		if (empty($acceptedTypes)) {
			return true;
		}

		foreach ($acceptedTypes as $acceptedType) {
					if ($this->checkIfContentTypesMatch($parsed, $acceptedType)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the preference value of the client for the provided content type.
	 *
	 * If the returned value is 0.0, the client does not accept the content type. If just checking if the client
	 * accepts the content type use HttpRequest::checkIfContentTypeIsPreferred() instead, since it's faster.
	 * If there was no Accept setHeader in the request, it will return 1.0 for a valid content type.
	 *
	 * @param string $contentType   The content type {@uses \YapepBase\Mime\MimeType::*}
	 *
	 * @return float
	 *
	 * @see HttpRequest::checkIfContentTypeIsPreferred()
	 */
	public function getContentTypePreferenceValue($contentType) {
		$parsed = $this->parseContentType($contentType);
		if (false === $parsed) {
			return 0.0;
		}

		if (isset($parsed['params']['q'])) {
			unset($parsed['params']['q']);
		}

		$preferenceValue = 0.0;
		$preferenceSpecificity = 0;
		$specificity = 0;

		$acceptedTypes = $this->getAcceptedContentTypes();

		if (empty($acceptedTypes)) {
			return 1.0;
		}

		foreach ($acceptedTypes as $acceptedType) {
			if (
				$this->checkIfContentTypesMatch($parsed, $acceptedType, $specificity)
				&& ($specificity >= $preferenceSpecificity || ($specificity == $preferenceSpecificity
					&& $acceptedType['params']['q'] > $preferenceValue))
			) {
				$preferenceValue = $acceptedType['params']['q'];
				$preferenceSpecificity = $specificity;
			}
		}

		return $preferenceValue;
	}

	/**
	 * Returns the protocol used in the request.
	 *
	 * @return string   The used protocol. {@uses self::PROTOCOL_*}
	 */
	public function getProtocol() {
		return ($this->getServer('HTTPS', 'off') == 'on' ? self::PROTOCOL_HTTPS : self::PROTOCOL_HTTP);
	}

}