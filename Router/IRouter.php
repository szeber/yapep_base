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
     * Returns a controller and an action.
     *
     * @param string $controller   $he controller class name. (Outgoing parameter)
     * @param string $action       The action name in the controller class. (Outgoing parameter)
     *
     * @return string   The controller and action separated by a '/' character.
     */
    public function getControllerAction(&$controller = null, &$action = null);
}