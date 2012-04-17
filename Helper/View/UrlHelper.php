<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Helper\View
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper\View;
use YapepBase\Application;

/**
 * UrlHelper class. Contains helper methods related to routing. Usable by the View layer.
 *
 * @package    YapepBase
 * @subpackage Helper\View
 */
class UrlHelper {

	/**
	 * Returns the target for the specified controller and action.
	 *
	 * If an exception occurs (eg. non-existing route), it returns '#' and triggers an error of it with level
	 * E_USER_ERROR.
	 *
	 * @param string $controller   Name of the controller
	 * @param string $action       Name of the action
	 * @param array  $params       Route params
	 * @param array  $getParams    The parameters should be placed in the url.
	 *
	 * @return string   The target
	 */
	public static function getRouteTarget($controller, $action, array $params = array(), array $getParams = array()) {
		try {
			$url = Application::getInstance()->getRouter()->getTargetForControllerAction($controller, $action, $params);
			return $url . (empty($getParams) ? '' : ('?' . http_build_query($getParams)));
		} catch (\Exception $exception) {
			trigger_error('Exception of type ' . get_class($exception) . ' occured in ' . __METHOD__, E_USER_ERROR);
			return '#';
		}
	}

	/**
	 * Returns the URL of the actual request.
	 *
	 * @param bool  $withParams    If TRUE the sent parameters will be incuded in the url.
	 * @param array $extraParams   List of extra GET parameters.
	 *
	 * @return string   The generated URL.
	 */
	public static function getCurrentUrl($withParams = true, array $extraParams = array()) {
		$request = Application::getInstance()->getRequest();

		$url = $request->getTarget();
		$params = array();

		if ($withParams) {
			$params = $request->getAllGet();
		}

		$params = array_merge($params, $extraParams);

		$url = empty($params) ? $url : $url . '?' . http_build_query($params);
		return $url;
	}

	/**
	 * Checks if the given uri is the same as the actual.
	 *
	 * @param string $controller   Name of the controller
	 * @param string $action       Name of the action
	 *
	 * @return bool
	 */
	public static function checkIsCurrentUri($controller, $action) {
		$request = Application::getInstance()->getRequest();

		$currentUri = $request->getTarget();
		$uriParams = $request->getAllUri();
		$givenUri = self::getRouteTarget($controller, $action, $uriParams);

		return $givenUri == $currentUri;
	}
}