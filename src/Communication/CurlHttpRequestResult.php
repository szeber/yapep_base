<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Communication
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Communication;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\CurlException;


/**
 * Result data object for an HTTP request sent via CURL.
 *
 * @package    YapepBase
 * @subpackage Communication
 */
class CurlHttpRequestResult {

	/**
	 * The full response with headers and body.
	 *
	 * @var string
	 */
	protected $response;

	/**
	 * The info array in the structure as provided by curl_getinfo()
	 *
	 * @var array
	 *
	 * @see curl_getinfo()
	 */
	protected $info;

	/**
	 * The error message for the curl request.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * Constructor.
	 *
	 * @param string $response   The full response with the headers.
	 * @param array  $info       The info array from curl_getinfo()
	 * @param string $error      The error message.
	 */
	public function __construct($response, array $info, $error) {
		$this->response = $response;
		$this->info     = $info;
		$this->error    = $error;
	}

	/**
	 * Returns the value of the specified info field from the array.
	 *
	 * @param string $field   Name of the field. Must match one of the keys from curl_getinfo().
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the key is not set.
	 * @see curl_getinfo()
	 */
	public function getInfoField($field) {
		if (!array_key_exists($field, $this->info)) {
			throw new ParameterException('Field does not exist in info array: ' . $field);
		}
	}

	/**
	 * Returns the response headers in a string
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\CurlException   If the request is a failed request.
	 */
	public function getResponseHeaders() {
		if ($this->error) {
			throw new CurlException('Failed getting the response headers for a failed cURL request.');
		}
		return (string)substr($this->response, 0, $this->info['header_size']);
	}

	/**
	 * Returns the HTTP status code for the request
	 *
	 * @return int
	 *
	 * @throws \YapepBase\Exception\CurlException   If the request is a failed request.
	 */
	public function getResponseCode() {
		if ($this->error) {
			throw new CurlException('Failed getting the response code for a failed cURL request.');
		}
		return $this->info['http_code'];
	}

	/**
	 * Returns whether the request was successful (no CURL error, and a 2xx status code)
	 *
	 * @return boolean
	 */
	public function isRequestSuccessful() {
		return (empty($this->error) && $this->info['http_code'] >= 200 && $this->info['http_code'] < 300);
	}

	/**
	 * Returns the response body.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\CurlException   If the request is a failed request.
	 */
	public function getResponseBody() {
		if ($this->error) {
			throw new CurlException('Failed getting the response body for a failed cURL request.');
		}
		return (string)substr($this->response, $this->info['header_size']);
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
