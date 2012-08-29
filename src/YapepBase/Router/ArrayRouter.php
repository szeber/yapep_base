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
use YapepBase\Request\IRequest;

/**
 * ArrayRouter class
 *
 * Routes a request based on the specified associative array.
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
class ArrayRouter implements IRouter {

	/**
	 * The request instance
	 *
	 * @var \YapepBase\Request\IRequest
	 */
	protected $request;

	/**
	 * The available routes
	 *
	 * @var array
	 */
	protected $routes;

	/**
	 * Constructor
	 *
	 * @param \YapepBase\Request\IRequest $request   The request instance
	 * @param array                       $routes    The list of available routes
	 */
	public function __construct(IRequest $request, array $routes) {
		$this->request = $request;
		$this->routes = $routes;
	}

	/**
	 * Returns the target of the request.
	 *
	 * @return string
	 */
	protected function getTarget() {
		return $this->request->getTarget();
	}

	/**
	 * Returns the method of the request.
	 *
	 * @return string
	 */
	protected function getMethod() {
		return $this->request->getMethod();
	}

	/**
	 * Returns a controller and an action for the request's target.
	 *
	 * @param string $controller   The controller class name. (Outgoing parameter)
	 * @param string $action       The action name in the controller class. (Outgoing parameter)
	 *
	 * @return string   The controller and action separated by a '/' character.
	 *
	 * @throws RouterException   On errors. (Includig if the route is not found)
	 */
	public function getRoute(&$controller = null, &$action = null) {
		$target = $this->getTarget();
		$method = $this->getMethod();

		// If the target doesn't start with a '/', add one
		if ('/' != substr($target, 0, 1)) {
			$target = '/' . $target;
		}

		foreach ($this->routes as $controllerAction => $path) {
			if ($this->testIfPathMatchesTarget($path, $method, $target)) {
				list($controller, $action) = explode('/', $controllerAction, 2);
				return $controllerAction;
			}
		}

		// There was no valid route
		throw new RouterException('No route found for path: ' . $target, RouterException::ERR_NO_ROUTE_FOUND);
	}

	/**
	 * Returns TRUE if the specified path matches the target, FALSE otherwise.
	 *
	 * @param string|array $path     The path to test. May be an array, if the action matches contains multiple targets.
	 * @param string       $method   The method.
	 * @param string       $target   The target.
	 *
	 * @return bool
	 */
	protected function testIfPathMatchesTarget($path, $method, $target) {
		// If the path is an array, check all values in it.
		if (is_array($path)) {
			foreach ($path as $value) {
				if ($this->testIfPathMatchesTarget($value, $method, $target)) {
					return true;
				}
			}
			return false;
		}

		if ('[' == substr($path, 0, 1)) {
			// This path is restricted to a method
			list($pathMethod, $path) = explode(']', substr($path, 1), 2);
			if ($method != $pathMethod) {
				// The method does not match, continue with the next path
				return false;
			}
		}

		$path = trim($path);

		if ('/' != substr($path, 0, 1)) {
			// If the path doesn't start with a '/', add one
			$path = '/' . $path;
		}

		if (false !== ($firstParamPos = strpos($path, '{'))) {
			// This path has params
			if (substr($path, 0, $firstParamPos) != substr($target, 0, $firstParamPos)) {
				// The static part of the path doesn't match, continue with the next path
				return false;
			}
			$regex = $this->getRegexForPath($path);
			$params = array();
			if (!preg_match($regex, $target, $params)) {
				// The target doesn't match the path
				return false;
			}
			foreach ($params as $name => $value) {
				if (is_numeric($name)) {
					continue;
				}
				$this->request->setParam($name, $value);
			}
			return true;
		} else {
			if ($path == $target) {
				return true;
			}
		}
	}

	/**
	 * Creates a regex from the parameterized route path.
	 *
	 * @param string $path   The path to process
	 *
	 * @return string   The regex created for the path
	 *
	 * @throws RouterException   On errors.
	 */
	protected function getRegexForPath($path) {
		$matches = array();
		if (
			!preg_match_all('/\{([-_.a-zA-Z0-9]+):([-_.a-zA-Z0-9]+)(\(([^}]*)\))?\}/', $path, $matches, PREG_SET_ORDER)
		) {
			throw new RouterException('Invalid param syntax in route: ' . $path, RouterException::ERR_SYNTAX_PARAM);
		}

		$pathRegex = '/^' . preg_quote($path, '/') . '$/';
		$params = array();
		foreach ($matches as $match) {
			switch ($match[2]) {
				case 'alpha':
					$pattern = '[[:alpha:]]+';
					break;

				case 'num':
					$pattern = '\d+';
					break;

				case 'alnum':
					$pattern = '[[:alnum:]]+';
					break;

				case 'regex':
					if (empty($match[3])) {
						throw new RouterException('Regex param type without pattern: ' . $path,
							RouterException::ERR_SYNTAX_PARAM);
					}
					$pattern = $match[3];
					break;

				case 'enum':
					if (empty($match[3])) {
						throw new RouterException('Enum param type without values: ' . $path,
							RouterException::ERR_SYNTAX_PARAM);
					}
					$pattern = $match[3];
					break;

				default:
					throw new RouterException('Invalid param type in route: ' . $match[1],
						RouterException::ERR_SYNTAX_PARAM);
					break;
			}
			$count = 0;
			$pathRegex = str_replace(preg_quote($match[0], '/'), '(?P<' . preg_quote($match[1], '/') . '>' . $pattern
				. ')', $pathRegex, $count);
			if (1 != $count) {
				throw new RouterException('Duplicate route param name: ' . $path, RouterException::ERR_SYNTAX_PARAM);
			}
		}
		return $pathRegex;
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
		if (!isset($this->routes[$key])) {
			throw new RouterException('No route found for controller and action: ' . $controller . '/' . $action,
				RouterException::ERR_NO_ROUTE_FOUND);
		}

		$target = false;
		if (is_array($this->routes[$key])) {
			foreach ($this->routes[$key] as $route) {
				$target = $this->getParameterizedRoute($route, $params);
				if (false !== $target) {
					break;
				}
			}
		} else {
			$target = $this->getParameterizedRoute($this->routes[$key], $params);
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
	 * Returns the route with the parameters, or FALSE if a param is missing, or not all params are used.
	 *
	 * @param string $route    The route.
	 * @param array  $params   The route parameters.
	 *
	 * @return bool|mixed
	 */
	public function getParameterizedRoute($route, array $params) {
		$route = preg_replace('/^\s*\[[^\]]+\]\s*/', '', $route);
		if (strstr($route, '{')) {
			foreach ($params as $key => $value) {
				$route = preg_replace('/\{' . preg_quote($key, '/') . ':[^}]+\}/', $value, $route);
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