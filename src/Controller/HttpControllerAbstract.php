<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\Application;

use YapepBase\Controller\Exception\IncompatibleRequestException;
use YapepBase\Controller\Exception\IncompatibleResponseException;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\RouterException;
use YapepBase\Request\HttpRequest;
use YapepBase\Request\IRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\IResponse;

/**
 * Base class for HTTP controllers.
 */
abstract class HttpControllerAbstract extends ControllerAbstract
{
    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function setRequest(IRequest $request)
    {
        if (!($request instanceof HttpRequest)) {
            throw new IncompatibleRequestException($request, HttpRequest::class);
        }
        return parent::setRequest($request);
    }

    public function setResponse(IResponse $response)
    {
        if (!($response instanceof HttpResponse)) {
            throw new IncompatibleResponseException($response, HttpResponse::class);
        }
        return parent::setResponse($response);
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
    ): void
    {
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
}
