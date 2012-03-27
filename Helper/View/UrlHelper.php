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
	 * Returns the target for the specified controller and action
	 *
	 * @param string $controller   Name of the controller
	 * @param string $action       Name of the action
	 * @param array  $params       Route params
	 *
	 * @return string   The target
	 */
	public static function getRouteTarget($controller, $action, $params = array()) {
		return Application::getInstance()->getRouter()->getTargetForControllerAction($controller, $action, $params);
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
}