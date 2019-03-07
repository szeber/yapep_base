<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\Application;

use YapepBase\Exception\ParameterException;
use YapepBase\Response\HttpResponse;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * Base class for HTTP controllers.
 */
abstract class HttpControllerAbstractAbstract extends ControllerAbstract
{
    /**
     * The request instance
     *
     * @var \YapepBase\Request\HttpRequest
     */
    protected $request;

    /**
     * The response instance
     *
     * @var \YapepBase\Response\HttpResponse
     */
    protected $response;

    public function setRequest(IRequest $request): void
    {
        if (!($request instanceof HttpRequest)) {
            throw new ParameterException('Http Controller should only use Http request');
        }
        parent::setRequest($request);
    }

    public function setResponse(IResponse $response): void
    {
        if (!($response instanceof HttpResponse)) {
            throw new ParameterException('Http Controller should only use Http response');
        }
        parent::setResponse($response);
    }

    /**
     * Redirects the client to the specified URL.
     *
     * @param string $url        The URL to redirect to.
     * @param int    $statusCode The status code of the redirect (3XX).
     *
     * @return void
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     */
    protected function redirectToUrl($url, $statusCode = 303)
    {
        $this->response->redirect($url, $statusCode);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * Redirects the client to the URL specified by the controller and action.
     *
     * @param string $controller  The name of the controller.
     * @param string $action      The action of the controller.
     * @param array  $routeParams Associative array containing the route parameters for the URL.
     * @param array  $getParams   Associative array containing the GET parameters for the URL.
     * @param string $anchor      The anchor for the URL
     * @param int    $statusCode  The status code of the redirect (3XX).
     *
     * @return void
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     * @throws \YapepBase\Exception\RouterException     If there was an error creating the URL.
     */
    protected function redirectToRoute(
        $controller,
        $action,
        $routeParams = [],
        $getParams = [],
        $anchor = '',
        $statusCode = 303
    ) {
        $url = Application::getInstance()->getRouter()->getTargetForControllerAction($controller, $action,
            $routeParams);
        if (!empty($getParams)) {
            $url .= '?' . \http_build_query($getParams, null, '&');
        }
        if (!empty($anchor)) {
            $url .= '#' . $anchor;
        }
        $this->redirectToUrl($url, $statusCode);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
