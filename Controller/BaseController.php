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
use YapepBase\View\IView;
use YapepBase\Application;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\ControllerException;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * Base class for generic controllers.
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
     * @param \YapepBase\Request\HttpRequest|\YapepBase\Request\IRequest     $request    The request object.
     * @param \YapepBase\Response\HttpResponse|\YapepBase\Response\IResponse $response   The response object.
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
     * @throws \YapepBase\Exception\RedirectException     On redirections.
     * @throws \Exception                                 On non-framework related errors.
     */
    public function run($action) {
        $methodName = $this->getActionPrefix() . $action;
        if (!method_exists($this, $methodName)) {
            throw new ControllerException('Action ' . $methodName . ' does not exist',
                ControllerException::ERR_ACTION_NOT_FOUND);
        }
        $this->before();
        $result = $this->runAction($methodName);
        if (!empty($result) && !is_string($result) && !($result instanceof IView)) {
            throw new ControllerException('Result of the action is not an instance of IView or string',
                ControllerException::ERR_INVALID_ACTION_RESULT);
        }
        if (!empty($result)) {
            if (is_string($result)) {
                $this->response->setRenderedBody($result);
            } else {
                $this->response->setBody($result);
            }
        }
        $this->after();
    }

    /**
     * Runs the action and returns the result as an IView instance
     *
     * @param string $methodName
     *
     * @return IView
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function runAction($methodName) {
        return $this->$methodName();
    }

    /**
     * Does an internal redirect (forwards the call to another controller and action).
     *
     * Be careful to set the route params in the request before calling this method, if the target action uses any.
     * The called action will use the same request and response objects.
     *
     * @param string $controllerName   The name of the controller.
     * @param string $action
     *
     * @throws \YapepBase\Exception\RedirectException     To stop execution of the controller.
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function internalRedirect($controllerName, $action) {
        $controller = Application::getInstance()->getDiContainer()->getController($controllerName, $this->request,
            $this->response);
        $controller->run($action);
        throw new RedirectException($controllerName . '/' . $action, RedirectException::TYPE_INTERNAL);
    }

    /**
     * Stores one ore more value(s).
     *
     * @param string $nameOrData   The name of the key, or the storable data in an associative array.
     * @param mixed  $value        The value.
     *
     * @throws \Exception   If the key already exist.
     *
     * @return void
     */
    public function setToView($nameOrData, $value = null) {
        Application::getInstance()->getDiContainer()->getViewDo()->set($nameOrData, $value);
    }
}