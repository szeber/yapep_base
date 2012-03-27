<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Request
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;

/**
 * Request interface
 *
 * @package    YapepBase
 * @subpackage Request
 */
interface IRequest {

	/** CLI method. Used by CLI requests. */
	const METHOD_CLI = 'CLI';
	/** GET HTTP method. */
	const METHOD_HTTP_GET = 'GET';
	/** POST HTTP method. */
	const METHOD_HTTP_POST = 'POST';
	/** PUT HTTP method. */
	const METHOD_HTTP_PUT = 'PUT';
	/** HEAD HTTP method. */
	const METHOD_HTTP_HEAD = 'HEAD';
	/** OPTIONS HTTP method. */
	const METHOD_HTTP_OPTIONS = 'OPTIONS';
	/** DELETE HTTP method. */
	const METHOD_HTTP_DELETE = 'DELETE';

	/** HTTP protocol */
	const PROTOCOL_HTTP = 'http';
	/** HTTPS protocol */
	const PROTOCOL_HTTPS = 'https';
	/** CLI request */
	const PROTOCOL_CLI = 'cli';

	/**
	 * Returns the target of the request. (eg the URI for HTTP requests)
	 *
	 * @return string   The target of the request.
	 */
	public function getTarget();

	/**
	 * Returns the method of the request
	 *
	 * @return string   {@uses self::METHOD_*}
	 */
	public function getMethod();

	/**
	 * Retruns the specified route param, or the default value if it's not set.
	 *
	 * @param string $name      The name of the cookie.
	 * @param mixed  $default   The default value, if the parameter is not set.
	 *
	 * @return mixed
	 */
	public function getParam($name, $default = null);

	/**
	 * Sets a route param
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setParam($name, $value);

	/**
	 * Returns the protocol used in the request.
	 *
	 * @return string   The used protocol. {@uses self::PROTOCOL_*}
	 */
	public function getProtocol();
}