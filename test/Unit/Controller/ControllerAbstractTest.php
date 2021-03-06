<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller;

use Mockery\MockInterface;
use YapepBase\Controller\Exception\ActionNotFoundException;
use YapepBase\Controller\Exception\InvalidActionResultException;
use YapepBase\Exception\RedirectException;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\Router\IRouter;
use YapepBase\Test\Unit\TestAbstract;

class ControllerAbstractTest extends TestAbstract
{
    /** @var ControllerAbstractStub */
    protected $controller;
    /** @var MockInterface */
    protected $request;
    /** @var MockInterface */
    protected $response;
    /** @var string */
    protected $redirectUrl = '/test';
    /** @var int */
    protected $redirectStatusCode = 301;
    /** @var string */
    protected $controllerToRedirect = 'IndexController';
    /** @var string */
    protected $actionToRedirect = 'Index';

    protected function setUp(): void
    {
        parent::setUp();

        $this->request  = \Mockery::mock(IRequest::class);
        $this->response = \Mockery::mock(IResponse::class);

        $this->controller = new ControllerAbstractStub();
        $this->controller->setRequest($this->request);
        $this->controller->setResponse($this->response);
    }

    public function testRunWhenCalledNonExistentAction_shouldThrowException()
    {
        $this->expectException(ActionNotFoundException::class);
        $this->controller->run('nonExistent');
    }

    public function testRunWhenStringReturnedByAction_shouldSetToResponse()
    {
        $this->expectStringResponseSet();
        $this->controller->run('testWithStringResult');
    }

    public function testRunWhenRenderableReturnedByAction_shouldSetToResponse()
    {
        $this->expectRenderableResponseSet();
        $this->controller->run('testWithRenderableResult');
    }

    public function testRunWhenInvalidReturnedByAction_shouldThrowException()
    {
        $this->expectException(InvalidActionResultException::class);
        $this->controller->run('testInvalidResult');
    }

    public function testRun_shouldCallHooksInOrder()
    {
        $controller = $this->expectHooksCalledInOrder();

        $this->expectResponseUsedOnMock($controller);
        $this->expectStringResponseSet();

        $controller->run('testWithStringResult');
    }

    public function testInternalRedirect_shouldRunActionAndThrowException()
    {
        $controllerClassName = ControllerAbstractStub::class;
        $action              = 'testRedirectedTo';

        $this->expectExceptionObject(new RedirectException($controllerClassName . '/' . $action, RedirectException::TYPE_INTERNAL));
        $this->controller->internalRedirect($controllerClassName, 'testRedirectedTo');

        $this->assertTrue($this->controller->isRedirected);
    }

    public function testRedirectToUrl_shouldRedirect()
    {
        $this->expectRedirect($this->redirectUrl);
        $this->controller->redirectToUrl($this->redirectUrl, $this->redirectStatusCode);
    }

    public function testRedirectToRoute_shouldRedirectToRetrievedUrl()
    {
        $routerParams = ['test' => 1];
        $this->expectGetPathFromRouter($routerParams);
        $this->expectRedirect($this->redirectUrl);

        $this->controller->redirectToRoute($this->controllerToRedirect, $this->actionToRedirect, $routerParams, [], '', $this->redirectStatusCode);
    }

    public function testRedirectToRouteWhenGetParamsGiven_shouldAddToUrl()
    {
        $getParams = ['test' => 1];
        $url       = $this->redirectUrl . '?test=1';

        $this->expectGetPathFromRouter([]);
        $this->expectRedirect($url);
        $this->controller->redirectToRoute($this->controllerToRedirect, $this->actionToRedirect, [], $getParams, '', $this->redirectStatusCode);
    }

    public function testRedirectToRouteWhenAnchorGiven_shouldAddToUrl()
    {
        $anchor = 'test';
        $url    = $this->redirectUrl . '#test';

        $this->expectGetPathFromRouter([]);
        $this->expectRedirect($url);
        $this->controller->redirectToRoute($this->controllerToRedirect, $this->actionToRedirect, [], [], $anchor, $this->redirectStatusCode);
    }

    protected function expectStringResponseSet()
    {
        $this->response
            ->shouldReceive('setRenderedBody')
            ->once()
            ->with($this->controller->resultString);
    }

    protected function expectRenderableResponseSet()
    {
        $this->response
            ->shouldReceive('setBody')
            ->once()
            ->with($this->controller->resultView);
    }

    protected function expectResponseUsedOnMock($controllerMock)
    {
        $reflectionClass = new \ReflectionClass($controllerMock);

        $property = $reflectionClass->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($controllerMock, $this->response);
        $property->setAccessible(false);
    }

    protected function expectHooksCalledInOrder(): ControllerAbstractStub
    {
        return \Mockery::mock(ControllerAbstractStub::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('runBeforeAction')
                ->ordered()
                ->getMock()
            ->shouldReceive('runBeforeResultSetToResponse')
                ->ordered()
                ->getMock()
            ->shouldReceive('runAfterResultSetToResponse')
                ->ordered()
                ->getMock();
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
            ->with($this->controllerToRedirect, $this->actionToRedirect, $params)
            ->andReturn($this->redirectUrl)
            ->getMock();

        $this->pimpleContainer->setRouter($router);
    }
}
