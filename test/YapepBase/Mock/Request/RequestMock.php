<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Request
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Mock\Request;
use YapepBase\Request\IRequest;

/**
 * RequestMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Request
 * @codeCoverageIgnore
 */
class RequestMock implements IRequest {

	/**
	 * The method for the request.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The target for the request.
	 *
	 * @var string
	 */
	protected $target;

	/**
	 * The params for the request.
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * The protocol for the request
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * Constructor
	 *
	 * @param string $target
	 * @param string $method
	 */
	public function __construct($target, $method = IRequest::METHOD_HTTP_GET, $protocol = IRequest::PROTOCOL_HTTP) {
		$this->target = $target;
		$this->method = $method;
	}

	/**
	 * Returns the target of the request.
	 *
	 * @return string   The target of the request.
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * Returns the method of the request
	 *
	 * @return string   {@uses self::METHOD_*}
	 */
	public function getMethod() {
		return $this->method;
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
		if (!isset($this->params[$name])) {
			return $default;
		}
		return $this->params[$name];
	}

	/**
	 * Sets a route param
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setParam($name, $value) {
		$this->params[$name] = $value;
	}

	/**
	 * Returns all the params that were set
	 *
	 * @return array
	 */
	public function getAllParams() {
		return $this->params;
	}

	/**
	 * Returns the protocol used in the request.
	 *
	 * @return string   The used protocol. {@uses self::PROTOCOL_*}
	 */
	public function getProtocol() {
		return $this->protocol;
	}

}