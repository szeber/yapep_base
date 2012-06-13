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
					$escapedParams = array();
					foreach ($parameters as $key => $value) {
						$escapedParams[] = urlencode($key) . '=' . urlencode($value);
					}
					$urlParts = parse_url($url);
					if (false === $urlParts || empty($urlParts['scheme']) || empty($urlParts['host'])) {
						trigger_error('Invalid URL: ' . $url, E_USER_ERROR);
						die;
					}
					if (!empty($urlParts['query'])) {
						$escapedParams = array_merge(explode('&', $urlParts['query']), $escapedParams);
					}
					$urlParts['query'] = implode('&', $escapedParams);

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
					$escapedParams = array();
					foreach ($parameters as $key => $value) {
						$escapedParams[] = urlencode($key) . '=' . urlencode($value);
					}
					$options[CURLOPT_POSTFIELDS] = implode('&', $escapedParams);
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

		$this->url = $url;

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
	 * Sends the request.
	 *
	 * @return boolean   TRUE on success, FALSE on failure
	 */
	public function send() {
		$result = curl_exec($this->curl);
		if (false === $result) {
			$this->error = curl_error($this->curl);
			throw new \YapepBase\Exception\Exception(curl_error($this->curl));
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