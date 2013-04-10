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

/**
 * Mock class for the CurlHttpWrapper.
 *
 * @package    YapepBase
 * @subpackage Communication
 */
class CurlHttpWrapperMock extends CurlHttpWrapper {

	/**
	 * @param string $responseBody      The response body.
	 * @param int    $responseCode      The response code.
	 * @param array  $responseHeaders   The response headers.
	 * @param string $errorMessage      The error message from curl. If set, an exception will be thrown during send().
	 */
	public function __construct(
		$responseBody, $responseCode = 200, array $responseHeaders = array(), $errorMessage = null
	) {
		$this->responseBody    = $responseBody;
		$this->responseInfo    = array('http_code' => $responseCode);
		$this->responseHeaders = $responseHeaders;
		$this->error           = $errorMessage;
	}

	/**
	 * Sends the request.
	 *
	 * @return boolean   TRUE on success, FALSE on failure
	 *
	 * @throws \YapepBase\Exception\Exception   If there was an error.
	 */
	public function send() {
		if (!empty($this->error)) {
			throw new \YapepBase\Exception\Exception('Curl Error:' . $this->error);
		}
		return true;
	}

}
