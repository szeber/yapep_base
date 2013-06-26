<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Communication
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Communication;

use YapepBase\Communication\CurlFactory;
use YapepBase\Communication\CurlHttpWrapper;
use YapepBase\Exception\Exception;

/**
 * Mock class for the CurlFactory
 *
 * @package    YapepBase
 * @subpackage Communication
 */
class CurlFactoryMock extends CurlFactory {

	/**
	 * Stores the curl instances, that will be returned.
	 *
	 * @var array
	 */
	protected $instances = array();

	/**
	 * Returns a new CurlHttpWrapper instance.
	 *
	 * @param string $method              The request method {@uses self::METHOD_*}.
	 * @param string $url                 The URL of the request.
	 * @param array  $parameters          The GET or POST parameters for the request.
	 * @param array  $additionalHeaders   Additional HTTP headers for the request.
	 * @param array  $extraOptions        Extra options for the request. The options must be in an associative array,
	 *                                    the key must be a valid CURL option name, and the value the value for that key
	 *
	 * @return \YapepBase\Communication\CurlHttpWrapper
	 */
	public function get($method, $url, $parameters = array(), $additionalHeaders = array(), $extraOptions = array()) {
		if (empty($this->instances)) {
			throw new Exception('No CURL wrapper instance is set');
		}
		return array_shift($this->instances);
	}

	/**
	 * Adds a curl wrapper instance that will be returned.
	 *
	 * @param CurlHttpWrapper $curlWrapper   The instance to return via the get() method.
	 *
	 * @return void
	 */
	public function addWrapper(CurlHttpWrapper $curlWrapper) {
		$this->instances[] = $curlWrapper;
	}

	/**
	 * Clears any previously set curl instances.
	 *
	 * @return void
	 */
	public function clear() {
		$this->instances = array();
	}

}
