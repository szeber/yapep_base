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

/**
 * Reverse router interface.
 *
 * @package    YapepBase
 * @subpackage Router
 */
interface IReverseRouter {

	/**
	 * Returns the target (eg. URL) for the controller and action
	 *
	 * @param string $controller   The name of the controller
	 * @param string $action       The name of the action
	 * @param array  $params       Associative array with the route params, if they are required.
	 *
	 * @return string   The target.
	 *
	 * @throws \YapepBase\Exception\RouterException   On errors. (Includig if the route is not found)
	 */
	public function getTargetForControllerAction($controller, $action, $params = array());
}