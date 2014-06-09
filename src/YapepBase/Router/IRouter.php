<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Router
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;


/**
 * Router interface
 *
 * @package    YapepBase
 * @subpackage Router
 */
interface IRouter extends IReverseRouter {

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
	public function getRoute(&$controller = null, &$action = null, $uri = null);
}