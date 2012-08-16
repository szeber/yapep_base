<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Response
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Response;

use YapepBase\Config;

/**
 * This class uses standard PHP functions to return content to the browser.
 *
 * Configuration options:
 *
 * <ul>
 *     <li>system.output.gzip: If set to TRUE the output will be sent gzipped or deflated if the client supports it</li>
 * </ul>
 *
 * @package      YapepBase
 * @subpackage   Response
 * @codeCoverageIgnore
 */
class PhpOutput implements IOutput {

	/**
	 * setHeader() is used to send a raw HTTP setHeader. See the » HTTP/1.1 specification for more information on HTTP
	 * headers.
	 *
	 * @param string $string         The setHeader string to set.
	 * @param bool   $replace        If this is set, current headers are replaced.
	 *                               Defaults to true.
	 * @param int    $responseCode   The response code to set.
	 *
	 * @return void
	 */
	public function setHeader($string, $replace = true, $responseCode = null) {
		header($string, $replace, $responseCode);
	}

	/**
	 * Send a cookie to the browser.
	 *
	 * @param string $name       The name of the cookie.
	 * @param string $value      The value of the cookie.
	 * @param int    $expire     The time the cookie expires. This is a Unix
	 *                           timestamp so is in number of seconds since the
	 *                           epoch. In other words, you'll most likely set
	 *                           this with the time() function plus the number of
	 *                           seconds before you want it to expire. Or you
	 *                           might use mktime(). time()+60*60*24*30 will set
	 *                           the cookie to expire in 30 days. If set to 0, or
	 *                           omitted, the cookie will expire at the end of the
	 *                           session (when the browser closes).
	 * @param string $path       The path on the server in which the cookie will
	 *                           be available on. If set to '/', the cookie will
	 *                           be available within the entire domain. If set to
	 *                           '/foo/', the cookie will only be available within
	 *                           the /foo/ directory and all sub-directories such
	 *                           as /foo/bar/ of domain. The default value is the
	 *                           current directory that the cookie is being set
	 *                           in.
	 * @param string $domain     The domain that the cookie is available to. To
	 *                           make the cookie available on all subdomains of
	 *                           example.com (including example.com itself) then
	 *                           you'd set it to '.example.com'. Although some
	 *                           browsers will accept cookies without the initial
	 *                           ., RFC 2109 requires it to be included. Setting
	 *                           the domain to 'www.example.com' or
	 *                           '.www.example.com' will make the cookie only
	 *                           available in the www subdomain.
	 * @param bool   $secure     Indicates that the cookie should only be
	 *                           transmitted over a secure HTTPS connection from the
	 *                           client. When set to TRUE, the cookie will only be
	 *                           set if a secure connection exists.
	 * @param bool   $httpOnly   When TRUE the cookie will be made accessible only
	 *                           through the HTTP protocol. This means that the
	 *                           cookie won't be accessible by scripting
	 *                           languages, such as JavaScript. It has been
	 *                           suggested that this setting can effectively help
	 *                           to reduce identity theft through XSS attacks
	 *                           (although it is not supported by all browsers),
	 *                           but that claim is often disputed.
	 *
	 * @return bool
	 *
	 * @see http://php.net/setcookie
	 */
	public function setCookie($name, $value = '', $expire = 0, $path = '/',
		$domain = '', $secure = false, $httpOnly = false) {
		return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Outputs (echoes) all parameters.
	 *
	 * @return void
	 */
	public function out() {
		if (Config::getInstance()->get('system.output.gzip', false)) {
			ob_start('ob_gzhandler');
		} else {
			ob_start();
		}
		foreach (func_get_args() as $string) {
			echo $string;
		}
		ob_end_flush();
	}
}
