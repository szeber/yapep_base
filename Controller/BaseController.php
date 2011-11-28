<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;
use YapepBase\Exception\RedirectException;

use YapepBase\Exception\ControllerException;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * BaseController class
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class BaseController implements IController {

    /**
     * The request instance
     *
     * @var \YapepBase\Request\IRequest;
     */
    protected $request;

    /**
     * The response instance
     *
     * @var \YapepBase\Response\IResponse
     */
    protected $response;

    /**
     * Constructor.
     *
     * @param \YapepBase\Request\HttpRequest   $request    The request object. Must be a HttpRequest or descendant.
     * @param \YapepBase\Response\HttpResponse $response   The response object. Must be a HttpResponse or descendant.
     *
     * @throws \YapepBase\Exception\ControllerException   On error. (eg. incompatible request or response object)
     */
    public function __construct(IRequest $request, IResponse $response) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Runs before the action.
     *
     * @throws \YapepBase\Exception\ControllerException   On error.
     */
    protected function before() {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs after the action
     *
     * @throws \YapepBase\Exception\ControllerException   On error.
     */
    protected function after() {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Returns the controller specific prefix
     *
     * @return string
     */
    protected function getActionPrefix() {
        return 'do';
    }

    /**
     * Runs the specified action
     *
     * @param string $action   The name of the action (without the controller specific prefix)
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    public function run($action) {
        $methodName = $this->getActionPrefix() . $action;
        if (!method_exists($this, $methodName)) {
            throw new ControllerException('Action ' . $methodName . ' does not exist',
                ControllerException::ERR_ACTION_NOT_FOUND);
        }
        try {
            $this->before();
            $this->$methodName();
            $this->after();
        } catch (RedirectException $exception) {
            // This is a redirect, we don't have to do handle it.
        }
    }

    /**
     * Does an internal redirect (forwards the call to another controller and action).
     *
     * Be careful to set the route params in the request before calling this method, if the target action uses any.
     * The called action will use the same request and response objects.
     *
     * @param string $controller   The name of the controller.
     * @param string $action
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function internalRedirect($controller, $action) {
        // TODO implement
        // get controller instance
        // run($action);
        throw new RedirectException($controller . '/' . $action, RedirectException::TYPE_INTERNAL);
    }
}