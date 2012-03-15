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
 * RoutingHelper class. Contains helper methods related to routing. Usable by the View layer.
 *
 * @package    YapepBase
 * @subpackage Helper\View
 */
class RoutingHelper {

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
}