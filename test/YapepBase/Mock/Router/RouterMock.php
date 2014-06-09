<?php

namespace YapepBase\Mock\Router;

/**
 * @codeCoverageIgnore
 */
class RouterMock implements \YapepBase\Router\IRouter {

	/**
	 * Returns a controller and an action for the request's target.
	 *
	 * @param string $controller   The controller class name. (Outgoing parameter)
	 * @param string $action       The action name in the controller class. (Outgoing parameter)
	 * @param string $uri          The uri to check. If not given, the current uri will be used.
	 *
	 * @return string   The controller and action separated by a '/' character.
	 *
	 * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
	 */
	public function getRoute(&$controller = null, &$action = null, $uri = null) {
		return 'NullController/null';
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
	 * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
	 */
	public function getTargetForControllerAction($controller, $action, $params = array()) {
		return '/';
	}
}