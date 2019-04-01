<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller;

use Mockery\MockInterface;
use YapepBase\Controller\Exception\IncompatibleRequestException;
use YapepBase\Controller\Exception\IncompatibleResponseException;
use YapepBase\Request\HttpRequest;
use YapepBase\Request\IRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\IResponse;
use YapepBase\Router\IRouter;
use YapepBase\Test\Unit\TestAbstract;

class HttpControllerAbstractTest extends TestAbstract
{
    /** @var MockInterface|HttpRequest */
    protected $request;
    /** @var MockInterface|HttpResponse */
    protected $response;
    /** @var string */
    protected $redirectUrl = '/test';
    /** @var int */
    protected $redirectStatusCode = 301;
    /** @var string */
    protected $controller = 'IndexController';
    /** @var string */
    protected $action = 'Index';

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = \Mockery::mock(HttpRequest::class);
        $this->response = \Mockery::mock(HttpResponse::class);
    }

    public function testSetRequestWhenNotHttpRequestGiven_shouldThrowException()
    {
        $controller = new HttpControllerAbstractStub();
        $request = \Mockery::mock(IRequest::class);

        $this->expectException(IncompatibleRequestException::class);
        $controller->setRequest($request);
    }

    public function testSetResponseWhenNotHttpResponseGiven_shouldThrowException()
    {
        $controller = new HttpControllerAbstractStub();
        $response   = \Mockery::mock(IResponse::class);

        $this->expectException(IncompatibleResponseException::class);
        $controller->setResponse($response);
    }

    public function testRedirectToUrl_shouldRedirect()
    {
        $this->expectRedirect($this->redirectUrl);
        $this->getController()->redirectToUrl($this->redirectUrl, $this->redirectStatusCode);
    }

    public function testRedirectToRoute_shouldRedirectToRetrievedUrl()
    {
        $routerParams = ['test' => 1];
        $this->expectGetPathFromRouter($routerParams);
        $this->expectRedirect($this->redirectUrl);

        $this->getController()->redirectToRoute($this->controller, $this->action, $routerParams, [], '', $this->redirectStatusCode);
    }

    public function testRedirectToRouteWhenGetParamsGiven_shouldAddToUrl()
    {
        $getParams = ['test' => 1];
        $url = $this->redirectUrl . '?test=1';

        $this->expectGetPathFromRouter([]);
        $this->expectRedirect($url);
        $this->getController()->redirectToRoute($this->controller, $this->action, [], $getParams, '', $this->redirectStatusCode);
    }

    public function testRedirectToRouteWhenAnchorGiven_shouldAddToUrl()
    {
        $anchor = 'test';
        $url = $this->redirectUrl . '#test';

        $this->expectGetPathFromRouter([]);
        $this->expectRedirect($url);
        $this->getController()->redirectToRoute($this->controller, $this->action, [], [], $anchor, $this->redirectStatusCode);
    }

    protected function getController(): HttpControllerAbstractStub
    {
        $controller = new HttpControllerAbstractStub();
        $controller->setRequest($this->request);
        $controller->setResponse($this->response);

        return $controller;
    }

    protected function expectRedirect(string $expectedUrl)
    {
        $this->response
            ->shouldReceive('redirect')
            ->once()
            ->with($expectedUrl, $this->redirectStatusCode);
    }

    protected function expectGetPathFromRouter(array $params = [])
    {
        /** @var IRouter|MockInterface $router */
        $router = \Mockery::mock(IRouter::class)
            ->shouldReceive('getPathByControllerAndAction')
            ->once()
            ->with($this->controller, $this->action, $params)
            ->andReturn($this->redirectUrl)
            ->getMock();

        $this->pimpleContainer->setRouter($router);
    }
}
