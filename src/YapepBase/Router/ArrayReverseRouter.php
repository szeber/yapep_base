<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;


use YapepBase\Exception\RouterException;

/**
 * Gets the route for the specified controller and action based on an associative array.
 *
 * The array's keys are in the Controller/Action format. The controller name should not include the namespace, and the
 * Action's name should not include the controller specific action prefix.
 * The array's values are the routes in the following format: [METHOD]URI
 * The method is optional (if present, the brackets are required). The URI may contain parameters in the format:
 * {paramName:paramType(options)}
 * The valid paramTypes are:
 *     <ul>
 *         <li>num: Any number. No options are allowed.</li>
 *         <li>alpha: Any alphabetic character. No options are allowed.</li>
 *         <li>alnum: Any alphanumeric character. No options are allowed.</li>
 *         <li>enum: Enumeration of the values in the options. The enumeration values should be separated by
 *                   the '|' character in the options string. Any '/' characters should be escaped.</li>
 *         <li>regex: Regular expression. The pattern should be included in the options string. The pattern should
 *                    not contain delimiters, and should be escaped for a '/' delimiter. It may not contain '{' or '}'
 *                    characters.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Router
 */
class ArrayReverseRouter implements IReverseRouter {

	/**
	 * The available routes
	 *
	 * @var array
	 */
	protected $routes;

	/**
	 * Constructor
	 *
	 * @param array                       $routes    The list of available routes
	 */
	public function __construct(array $routes) {
		$this->routes = $routes;
	}

	/**
	 * Returns the target (eg. URL) for the controller and action
	 *
	 * @param string $controller   The name of the controller
	 * @param string $action       The name of the action
	 * @param array  $params       Associative array with the route params, if they are required.
	 *
	 * @return string   The target.
	 *
	 * @throws RouterException   On errors. (Including if the route is not found)
	 */
	public function getTargetForControllerAction($controller, $action, $params = array()) {
		$key = $controller . '/' . $action;
		$routes = $this->getRouteArray();
		if (!isset($routes[$key])) {
			throw new RouterException('No route found for controller and action: ' . $controller . '/' . $action,
				RouterException::ERR_NO_ROUTE_FOUND);
		}

		$target = false;
		if (is_array($routes[$key])) {
			foreach ($routes[$key] as $route) {
				$target = $this->getParameterizedRoute($route, $params);
				if (false !== $target) {
					break;
				}
			}
		} else {
			$target = $this->getParameterizedRoute($routes[$key], $params);
		}

		if (false === $target) {
			throw new RouterException(
				'No exact route match for controller and action: ' . $controller . '/' . $action . ' with params: '
					. implode(', ', array_keys($params)), RouterException::ERR_MISSING_PARAM
			);
		}

		if ('/' != substr($target, 0, 1)) {
			$target = '/' . $target;
		}
		return $target;
	}

	/**
	 * Returns the array of routes.
	 *
	 * @return array
	 */
	protected function getRouteArray() {
		return $this->routes;
	}

	/**
	 * Returns the route with the parameters, or FALSE if a param is missing, or not all params are used.
	 *
	 * @param string $route    The route.
	 * @param array  $params   The route parameters.
	 *
	 * @return bool|mixed
	 */
	protected function getParameterizedRoute($route, array $params) {
		$route = preg_replace('/^\s*\[[^\]]+\]\s*/', '', $route);
		if (strstr($route, '{')) {
			foreach ($params as $key => $value) {
				$count = 0;
				$route = preg_replace('/\{' . preg_quote($key, '/') . ':[^}]+\}/', $value, $route, -1, $count);
				if ($count < 1) {
					return false;
				}
			}
			if (strstr($route, '{')) {
				return false;
			}
		} elseif (!empty($params)) {
			return false;
		}
		return $route;
	}
}