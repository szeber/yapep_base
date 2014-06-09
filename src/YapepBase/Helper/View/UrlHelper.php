<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Helper\View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper\View;

use YapepBase\Exception\Exception;
use YapepBase\Helper\HelperAbstract;
use YapepBase\Application;
use YapepBase\Router\ILanguageReverseRouter;

/**
 * UrlHelper class. Contains helper methods related to routing. Usable by the View layer.
 *
 * @package    YapepBase
 * @subpackage Helper\View
 */
class UrlHelper extends HelperAbstract {

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
	public function getRouteTarget($controller, $action, array $params = array(), array $getParams = array()) {
		try {
			$url = Application::getInstance()->getRouter()->getTargetForControllerAction($controller, $action, $params);
			return $url . (empty($getParams) ? '' : ('?' . http_build_query($getParams)));
		} catch (\Exception $exception) {
			trigger_error('Exception of type ' . get_class($exception) . ' occured in ' . __METHOD__
				. '. Requested controller/action: ' . $controller . '/' . $action, E_USER_ERROR);
			return '#';
		}
	}

	/**
	 * Returns the target for the specified controller and action in the given language
	 *
	 * @param string $controller   Name of the controller
	 * @param string $action       Name of the action
	 * @param string $language     Code of the language, the target is requested for.
	 * @param array  $params       Route params
	 * @param array  $getParams    The parameters should be placed in the url.
	 *
	 * @return string
	 */
	public function getRouteTargetInLanguage(
		$controller, $action, $language, array $params = array(), array $getParams = array()
	) {
		try {
			$router = Application::getInstance()->getRouter();
			if ($router instanceof ILanguageReverseRouter) {
				$url = $router->getTargetForControllerActionInLanguage($controller, $action, $language, $params);
				return $url . (empty($getParams) ? '' : ('?' . http_build_query($getParams)));
			} else {
				// TODO: Replace this after we moved to PHP 5.5, and we have a final block for try-catch [emul]
				trigger_error('The router is not an instance of ILanguageReverseRouter', E_USER_ERROR);
				return '#';
			}
		} catch (\Exception $exception) {
			trigger_error('Exception of type ' . get_class($exception) . ' occured in ' . __METHOD__
				. '. Requested controller/action: ' . $controller . '/' . $action, E_USER_ERROR);
			return '#';
		}
	}

	/**
	 * Returns the URL of the actual request.
	 *
	 * @param bool  $withParams    If TRUE the sent parameters will be included in the url.
	 * @param array $extraParams   List of extra GET parameters.
	 *                                If a value to a key is set to NULL, that parameter
	 *                                will be removed from the current URL.
	 *
	 * @return string   The generated URL.
	 */
	public function getCurrentUrl($withParams = true, array $extraParams = array()) {
		/** @var \YapepBase\Request\HttpRequest $request  */
		$request = Application::getInstance()->getRequest();

		$url = $request->getTarget();
		$params = array();

		if ($withParams) {
			$params = $request->getAllGet();
		}

		$params = array_merge($params, $extraParams);

		foreach ($params as $key => $value) {
			if ($value === null) {
				unset($params[$key]);
			}
		}

		$url = empty($params) ? $url : $url . '?' . http_build_query($params);
		return $url;
	}

	/**
	 * Checks if the given controller, action and URI params are the same as the current target.
	 *
	 * <b>Warning:</b> This method checks the originally requested URI, so it will not work on error pages.<br>
	 * <b>Warning:</b> This method can throw an Exception, if the provided controller is the errorcontroller,
	 * because by default the error pages don't have to be set in the routing table.
	 *
	 * @param string $controller   Name of the controller.
	 * @param string $action       Name of the action.
	 * @param array  $params       The URI parameters.
	 *
	 * @return bool
	 */
	public function checkIsCurrentUri($controller, $action, array $params) {
		$application = Application::getInstance();
		/** @var \YapepBase\Request\HttpRequest $request  */
		$request = $application->getRequest();
		$router = $application->getRouter();

		$uri = rtrim($request->getTarget(), '/');

		return $uri == rtrim($router->getTargetForControllerAction($controller, $action, $params), '/');
	}

	/**
	 * Checks if the given controller and action is the same as the actual.
	 *
	 * <b>Warning:</b> If you wan't to use this method on an ErrorPage it can throw an Exception,
	 * because by default the error pages don't have to be set in the routing table.
	 * So to avoid this, you just have to add your error pages into your routing table.
	 *
	 * @param string $controller   Name of the controller
	 * @param string $action       Name of the action
	 *
	 * @return bool
	 */
	public function checkIsCurrentControllerAction($controller, $action) {
		// Get the current controller and action
		$currentController = null;
		$currentAction = null;
		Application::getInstance()->getDispatchedAction($currentController, $currentAction);

		return $currentController == $controller && $currentAction == $action;
	}
}