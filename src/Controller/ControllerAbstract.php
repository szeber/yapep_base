<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;


use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\ControllerException;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\View\ViewAbstract;

/**
 * Base class for generic controllers.
 *
 * Configuration options:
 * <ul>
 *   <li>system.performStrictControllerActionNameValidation: If this option is TRUE, the action's name will be
 *                                                           validated in a case sensitive manner. This is recommended
 *                                                           for development, but not recommended for production as it
 *                                                           can cause errors, and will somewhat impact the performance.
 *                                                           Optional, defaults to FALSE.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class ControllerAbstract implements IController
{

    /** @var IRequest */
    protected $request;

    /** @var IResponse */
    protected $response;

    public function __construct()
    {
        Application::getInstance()->getDiContainer()->getViewDo()->clear();
    }

    public function setRequest(IRequest $request): void
    {
        $this->request = $request;
    }

    public function setResponse(IResponse $response): void
    {
        $this->response = $response;
    }


    /**
     * Runs before the action.
     *
     * @throws ControllerException   On error.
     */
    protected function before(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs after the action
     *
     * @throws ControllerException   On error.
     */
    protected function after(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs after the action but before the rendering.
     *
     * Can be useful to set collected data to the View.
     *
     * @throws ControllerException   On error.
     */
    protected function runBeforeRender(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs before the response is being set.
     *
     * Allows to modify the result of the action before it's set to response
     *
     * @todo Check this [emul]
     * @param ViewAbstract|string $actionResult The result of the called action
     *
     * @return void
     */
    protected function runBeforeResponseSet(&$actionResult): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Returns the controller specific prefix
     */
    protected function getActionPrefix(): string
    {
        return 'do';
    }

    public function run(string $action): void
    {
        $methodName = $this->getActionPrefix() . $action;
        if (!method_exists($this, $methodName)) {
            throw new ControllerException('Action ' . $methodName . ' does not exist in ' . get_class($this),
                ControllerException::ERR_ACTION_NOT_FOUND);
        }
        if (Config::getInstance()->get('system.performStrictControllerActionNameValidation', false)) {
            $reflection = new \ReflectionClass($this);
            $method     = $reflection->getMethod($methodName);
            if ($method->name != $methodName) {
                throw new ControllerException('Invalid case when running action ' . $methodName . ' in ' . get_class($this) . '. The valid case is: ' . $method->name,
                    ControllerException::ERR_ACTION_NOT_FOUND);
            }
        }
        $this->before();
        $result = $this->runAction($methodName);
        if (!empty($result) && !is_string($result) && !($result instanceof ViewAbstract)) {
            throw new ControllerException('Result of the action (' . get_class($this) . '/' . $action . ') is not an instance of ViewAbstract or string',
                ControllerException::ERR_INVALID_ACTION_RESULT);
        }

        // We called the run method, but we did not rendered the output yet
        $this->runBeforeRender();

        $this->runBeforeResponseSet($result);

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
     * Runs the action and returns the result as an ViewAbstract instance
     *
     * @throws ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function runAction(string $methodName): ViewAbstract
    {
        return $this->$methodName();
    }

    /**
     * Does an internal redirect (forwards the call to another controller and action).
     *
     * Be careful to set the route params in the request before calling this method, if the target action uses any.
     * The called action will use the same request and response objects.
     *
     * @throws \YapepBase\Exception\RedirectException     To stop execution of the controller.
     * @throws ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function internalRedirect(string $controllerName, string $action): void
    {
        Application::getInstance()->setDispatchedAction($controllerName, $action);
        $controller = Application::getInstance()->getDiContainer()->getController($controllerName, $this->request,
            $this->response);
        $controller->run($action);
        throw new RedirectException($controllerName . '/' . $action, RedirectException::TYPE_INTERNAL);
    }

    /**
     * Stores one ore more value(s).
     *
     * @throws \Exception   If the key already exists.
     */
    protected function setToView(string $nameOrData, $value = null): void
    {
        Application::getInstance()->getDiContainer()->getViewDo()->set($nameOrData, $value);
    }
}
