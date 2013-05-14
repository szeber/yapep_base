<?php
/**
 * This file is part of YAPEPBase
 *
 * @package      YapepBase
 * @subpackage   Communication
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Communication;

use YapepBase\Application;
use YapepBase\Debugger\IDebugger;
use YapepBase\Debugger\Item\CurlRequestItem;

/**
 * Wrapper class for sending HTTP requests with CURL.
 *
 * @package    YapepBase
 * @subpackage Communication
 */
class CurlHttpWrapper {

	/** GET method */
	const METHOD_GET = 'GET';
	/** POST method */
	const METHOD_POST = 'POST';

	/**
	 * The CURL connection resource
	 *
	 * @var resource
	 */
	protected $curl;

	/**
	 * The body of the response
	 *
	 * @var string
	 */
	protected $responseBody;

	/**
	 * The information array for the response
	 *
	 * @var array
	 */
	protected $responseInfo;

	/**
	 * The headers from the response
	 *
	 * @var string
	 */
	protected $responseHeaders;

	/**
	 * The CURL error message if there was any.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * The URL for the request.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The request method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The request parameters.
	 *
	 * @var array
	 */
	protected $parameters;

	/**
	 * The additional request headers for the request.
	 *
	 * @var array
	 */
	protected $additionalHeaders;

	/**
	 * The extra curl options for the request.
	 *
	 * @var array
	 */
	protected $extraOptions;

	/**
	 * The cookies that are going to be sent with the request.
	 *
	 * @var array
	 */
	protected $cookies = array();

	/**
	 * Constructor.
	 *
	 * @param string $method              The request method {@uses self::METHOD_*}.
	 * @param string $url                 The URL of the request.
	 * @param array  $parameters          The GET or POST parameters for the request.
	 * @param array  $additionalHeaders   Additional HTTP headers for the request.
	 * @param array  $extraOptions        Extra options for the request. The options must be in an associative array,
	 *                                    the key must be a valid CURL option name, and the value the value for that key
	 */
	public function __construct(
		$method, $url, $parameters = array(), $additionalHeaders = array(), $extraOptions = array()
	) {
		if (empty($extraOptions) || !is_array($extraOptions)) {
			$options = array();
		} else {
			$options = $extraOptions;
		}

		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_HEADER] = true;

		switch ($method) {
			case self::METHOD_GET:
				$options[CURLOPT_HTTPGET] = true;
				if (!empty($parameters)) {
					$query = http_build_query($parameters);
					$urlParts = parse_url($url);
					if (false === $urlParts || empty($urlParts['scheme']) || empty($urlParts['host'])) {
						// TODO Figure out why we are not just throwing an exception here [szeber]
						trigger_error('Invalid URL: ' . $url, E_USER_ERROR);
						exit;
					}
					$urlParts['query'] = empty($urlParts['query']) ? $query : $query . '&' . $urlParts['query'];

					// Rebuild the URL. If we have pecl_http with http_build_url use it,
					// otherwise use the PHP implementation.
					$url = (
					function_exists('http_build_url')
						? http_build_url($urlParts)
						: $this->buildUrl($urlParts)
					);
				}
				break;

			case self::METHOD_POST:
				$options[CURLOPT_POST] = true;
				if (empty($parameters)) {
					trigger_error('HTTP POST request without parameters', E_USER_WARNING);
				} else {
					$options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
				}
				break;

			default:
				trigger_error('Invalid HTTP method: ' . $method, E_USER_ERROR);
				die;
				break;
		}

		if (is_array($additionalHeaders) && !empty($additionalHeaders)) {
			$options[CURLOPT_HTTPHEADER] = array_values($additionalHeaders);
		}

		$this->url               = $url;
		$this->method            = $method;
		$this->parameters        = $parameters;
		$this->additionalHeaders = $additionalHeaders;
		$this->extraOptions      = $extraOptions;

		$this->curl = curl_init($url);
		curl_setopt_array($this->curl, $options);
	}

	/**
	 * Builds an url from an array returned by parse_url. Use http_build_url if it's available on the system.
	 *
	 * @param array $urlParts   The parts of the URL.
	 *
	 * @return string
	 */
	protected function buildUrl($urlParts) {
		if (empty($urlParts['host'])) {
			trigger_error('Building URL without a host', E_USER_ERROR);
			die;
		}
		$url = (empty($urlParts['scheme']) ? 'http' : $urlParts['scheme']) . '://';
		if (!empty($urlParts['user'])) {
			$url .= $urlParts['user'];
			if (!empty($urlParts['pass'])) {
				$url .= ':' . $urlParts['pass'];
			}
			$url .= '@';
		}
		$url .= $urlParts['host'];
		if (!empty($urlParts['port'])) {
			$url .= ':' . $urlParts['port'];
		}
		if (!empty($urlParts['path'])) {
			$url .= $urlParts['path'];
		}
		if (!empty($urlParts['query'])) {
			$url .= '?' . $urlParts['query'];
		}
		if (!empty($urlParts['fragment'])) {
			$url .= '#' . $urlParts['fragment'];
		}
		return $url;
	}

	/**
	 * Sets a single cookie.
	 *
	 * @param string $name    The cookie's name.
	 * @param string $value   The cookie's value.
	 *
	 * @return void
	 */
	public function setCookie($name, $value) {
		$this->cookies[$name] = $value;

	}

	/**
	 * Sets cookies from an associative array.
	 *
	 * @param array $cookies   The cookies to set.
	 *
	 * @return void
	 */
	public function setCookies(array $cookies) {
		$this->cookies = array_merge($this->cookies, $cookies);
	}

	/**
	 * Sends the request.
	 *
	 * @return boolean   TRUE on success, FALSE on failure
	 *
	 * @throws \YapepBase\Exception\Exception   If there was an error.
	 */
	public function send() {
		if (!empty($this->cookies)) {
			$cookies = array();
			foreach ($this->cookies as $name => $value) {
				$cookies[] = $name . '=' . $value;
			}
			curl_setopt($this->curl, CURLOPT_COOKIE, implode('; ', $cookies));
		}

		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		$startTime = microtime(true);
		$result = curl_exec($this->curl);


		if ($debugger !== false) {
			$debugger->addItem(new CurlRequestItem(CurlRequestItem::PROTOCOL_HTTP, $this->method, $this->url,
				$this->parameters, $this->additionalHeaders, $this->extraOptions, microtime(true) - $startTime));
		}

		if (false === $result) {
			$this->error = curl_error($this->curl);

			throw new \YapepBase\Exception\Exception('Curl Error:' . curl_error($this->curl));
		}

		$info = curl_getinfo($this->curl);
		curl_close($this->curl);

		$this->responseBody = (string)substr($result, $info['header_size']);
		$this->responseHeaders = (string)substr($result, 0, $info['header_size']);
		$this->responseInfo = $info;
		return true;
	}

	/**
	 * Returns the response headers in a string
	 *
	 * @return string
	 */
	public function getResponseHeaders() {
		return $this->responseHeaders;
	}

	/**
	 * Returns the HTTP status code for the request
	 *
	 * @return int
	 */
	public function getResponseCode() {
		return $this->responseInfo['http_code'];
	}

	/**
	 * Returns whether the request was successful (no CURL error, and a 2xx status code)
	 *
	 * @return boolean
	 */
	public function isRequestSuccessful() {
		return (empty($this->error) && $this->responseInfo['http_code'] >= 200
			&& $this->responseInfo['http_code'] < 300);
	}

	/**
	 * Returns the response body.
	 *
	 * @return string
	 */
	public function getResponseBody() {
		return $this->responseBody;
	}

	/**
	 * Returns the CURL error message for the request, if an error occured.
	 *
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

}