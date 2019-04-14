<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\Application;
use YapepBase\Controller\Exception\ActionNotFoundException;
use YapepBase\Controller\Exception\Exception;
use YapepBase\Controller\Exception\InvalidActionResultException;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\View\IRenderable;

/**
 * Base class for generic controllers.
 */
abstract class ControllerAbstract implements IController
{
    /** @var IRequest */
    protected $request;

    /** @var IResponse */
    protected $response;

    public function setRequest(IRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    public function setResponse(IResponse $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @throws ActionNotFoundException
     */
    public function run(string $action): void
    {
        $actionMethodName = $this->getActionMethodName($action);

        $this->runBeforeAction();

        $result = $this->$actionMethodName();

        $this->validateActionResult($action, $result);

        $this->runBeforeResultSetToResponse();

        if (!empty($result)) {
            if (is_string($result)) {
                $this->response->setRenderedBody($result);
            } else {
                $this->response->setBody($result);
            }
        }
        $this->runAfterResultSetToResponse();
    }

    public function getRequest(): IRequest
    {
        return $this->request;
    }

    public function getResponse(): IResponse
    {
        return $this->response;
    }

    /**
     * Runs before the action.
     *
     * @throws Exception
     */
    protected function runBeforeAction(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs after the action but before the result of the action is set to response.
     *
     * @throws Exception
     */
    protected function runBeforeResultSetToResponse(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs after the result of the action is set to response.
     *
     * @throws Exception
     */
    protected function runAfterResultSetToResponse(): void
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Runs before doing an internal redirection.
     */
    protected function runBeforeInternalRedirect()
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Does an internal redirect (forwards the call to another controller and action).
     *
     * @throws RedirectException     To stop execution of the controller.
     */
    protected function internalRedirect(string $controllerClassName, string $action): void
    {
        $this->runBeforeInternalRedirect();

        /** @var IController $controller */
        $controller = new $controllerClassName();
        $controller->run($action);

        throw new RedirectException($controllerClassName . '/' . $action, RedirectException::TYPE_INTERNAL);
    }

    /**
     * Returns the controller specific prefix
     */
    protected function getActionPrefix(): string
    {
        return 'do';
    }

    /**
     * Redirects the client to the specified URL.
     *
     * @throws RedirectException
     */
    protected function redirectToUrl(string $url, int $statusCode = 303): void
    {
        $this->response->redirect($url, $statusCode);
    }

    /**
     * Redirects the client to the URL specified by the controller and action.
     *
     * @throws RedirectException
     * @throws RouterException
     */
    protected function redirectToRoute(
        string $controller,
        string $action,
        array $routeParams = [],
        array $getParams = [],
        string $anchor = '',
        int $statusCode = 303
    ): void {
        $url = Application::getInstance()->getDiContainer()->getRouter()->getPathByControllerAndAction(
            $controller,
            $action,
            $routeParams
        );

        if (!empty($getParams)) {
            $url .= '?' . \http_build_query($getParams, '', '&');
        }
        if (!empty($anchor)) {
            $url .= '#' . $anchor;
        }
        $this->redirectToUrl($url, $statusCode);
    }

    /**
     * @throws ActionNotFoundException
     */
    private function getActionMethodName(string $action): string
    {
        $methodName = $this->getActionPrefix() . $action;
        if (!method_exists($this, $methodName)) {
            throw new ActionNotFoundException(get_class($this), $action);
        }

        return $methodName;
    }

    /**
     * @throws InvalidActionResultException
     */
    private function validateActionResult(string $action, $result)
    {
        if (!empty($result) && !is_string($result) && !($result instanceof IRenderable)) {
            throw new InvalidActionResultException(get_class($this), $action);
        }
    }
}
