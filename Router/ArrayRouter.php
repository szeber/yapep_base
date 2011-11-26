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
 * ArrayRouter class
 *
 * Routes a request based on the specified array.
 *
 * @package    YapepBase
 * @subpackage Router
 */
use YapepBase\Request\IRequest;

class ArrayRouter implements IRouter{

    /**
     * The request instance
     *
     * @var IRequest
     */
    protected $request;

    /**
     * Constructor
     *
     * @param IRequest $request   The request instance
     * @param array    $routes    The list of available routes
     */
    public function __construct(IRequest $request, array $routes) {
        $this->request = $request;
    }

    /**
     * Returns a controller and an action.
     *
     * @param string $controller   $he controller class name. (Outgoing parameter)
     * @param string $action       The action name in the controller class. (Outgoing parameter)
     *
     * @return string   The controller and action separated by a '/' character.
     */
    public function getControllerAction ($controller = null, $action = null)
    {
        // TODO implement
    }


}