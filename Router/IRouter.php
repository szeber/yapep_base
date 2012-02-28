<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Router;

/**
 * Router interface
 *
 * @package    YapepBase
 * @subpackage Router
 */
interface IRouter {

    /**
     * Returns a controller and an action for the request's target.
     *
     * @param string $controller   $he controller class name. (Outgoing parameter)
     * @param string $action       The action name in the controller class. (Outgoing parameter)
     *
     * @return string   The controller and action separated by a '/' character.
     *
     * @throws \YapepBase\Exception\RouterException   On errors. (Includig if the route is not found)
     */
    public function getRoute(&$controller = null, &$action = null);

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