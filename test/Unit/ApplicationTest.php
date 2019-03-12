<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Application;
use YapepBase\Controller\IController;
use YapepBase\Event\Event;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Exception\Exception;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\Router\IRouter;

class ApplicationTest extends TestAbstract
{
    /** @var Application */
    protected $application;
    /** @var MockInterface */
    protected $eventHandlerRegistry;

    /** @var string */
    protected $errorControllerName = 'ErrorController';
    /** @var string */
    protected $controllerName = 'TestController';
    /** @var string */
    protected $actionName = 'testAction';

    /** @var MockInterface */
    protected $request;
    /** @var MockInterface */
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = Application::getInstance();
        $this->registerEventHandler();
        $this->registerRequestAndResponse();
    }

    protected function registerEventHandler()
    {
        $this->eventHandlerRegistry = Mockery
            ::mock(EventHandlerRegistry::class)
            ->shouldReceive('getLastRaisedInMs')
            ->zeroOrMoreTimes()
            ->andReturn(1)
            ->getMock();
        $this->pimpleContainer->setEventHandlerRegistry($this->eventHandlerRegistry);
    }

    protected function registerRequestAndResponse()
    {
        $this->request  = Mockery::mock(IRequest::class);
        $this->response = Mockery::mock(IResponse::class);

        $this->pimpleContainer
            ->setRequest($this->request)
            ->setResponse($this->response);
    }

    public function testRunWhenErrorControllerNotSet_shouldThrowException()
    {
        $this->expectException(Exception::class);
        $this->application->run();

        $this->assertTrue($this->application->isStarted());
    }

    public function testGetDiContainerWhenSet_shouldReturnSetContainer()
    {
        $this->application->setDiContainer($this->diContainer);
        $result = $this->application->getDiContainer();

        $this->assertSame($this->diContainer, $result);
    }

    public function testRunWhenPerfect_shouldRunActionAndReturnResponse()
    {
        $this->setErrorController();
        $this->expectAllEventsRaised();
        $this->expectGetRoute();
        $this->expectRunActionOnController($this->controllerName, $this->actionName);
        $this->expectResponseSent();

        $this->application->run();
    }

    public function testRunWhenRouterThrowsNoRouteFoundException_shouldRun404ErrorAction()
    {
        $expectedException = new RouterException('', RouterException::ERR_NO_ROUTE_FOUND);

        $this->setErrorController();
        $this->expectAllEventsRaised();
        $this->expectGetRouteThrowsException($expectedException);
        $this->expectRunActionOnController($this->errorControllerName, '404');
        $this->expectResponseSent();

        $this->application->run();
    }

    public function testRunWhenRedirectExceptionThrown_shouldSendResponse()
    {
        $expectedException = new RedirectException('/', RedirectException::TYPE_INTERNAL);

        $this->setErrorController();
        $this->expectAllEventsRaised();
        $this->expectGetRouteThrowsException($expectedException);
        $this->expectResponseSent();

        $this->application->run();
    }

    public function testRunWhenExceptionThrownRequestIsNotHttp_shouldOutputError()
    {
        $expectedException = new Exception('');

        $this->setErrorController();
        $this->expectAllEventsRaised();
        $this->expectGetRouteThrowsException($expectedException);
        $this->expectErrorSentToOutput();

        $this->application->run();
    }

    protected function setErrorController()
    {
        $this->application->setErrorController($this->errorControllerName);
    }

    protected function expectAllEventsRaised()
    {
        $this->expectEventRaised(Event::TYPE_APPLICATION_BEFORE_RUN);
        $this->expectEventRaised(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN);
        $this->expectEventRaised(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN);
        $this->expectEventRaised(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND);
        $this->expectEventRaised(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND);
        $this->expectEventRaised(Event::TYPE_APPLICATION_AFTER_RUN);
    }

    protected function expectEventRaised(string $expectedType)
    {
        $event = new Event($expectedType);

        $this->eventHandlerRegistry
            ->shouldReceive('raise')
            ->with(Mockery::on(function () use ($expectedType, $event) {
                return $expectedType === $event->getType();
            }));
    }

    protected function expectGetRoute()
    {
        $router = Mockery
            ::mock(IRouter::class)
            ->shouldReceive('getRoute')
            ->once()
            ->with(
                Mockery::on(function (&$controller) {
                    $controller = $this->controllerName;

                    return true;
                }),
                Mockery::on(function (&$action) {
                    $action = $this->actionName;

                    return true;
                })
            )
            ->getMock();

        $this->pimpleContainer->setRouter($router);
    }

    protected function expectGetRouteThrowsException(\Exception $expectedException)
    {
        $router = Mockery
            ::mock(IRouter::class)
            ->shouldReceive('getRoute')
            ->once()
            ->andThrows($expectedException)
            ->getMock();

        $this->pimpleContainer->setRouter($router);
    }

    protected function expectRunActionOnController(string $controllerName, string $actionName)
    {
        $controllerMock = Mockery
            ::mock(IController::class)
            ->shouldReceive('setRequest')
                ->once()
                ->with(Mockery::on(function ($request) {
                    return $this->request === $request;
                }))
                ->getMock()
            ->shouldReceive('setResponse')
                ->once()
                ->with(Mockery::on(function ($response) {
                    return $this->response === $response;
                }))
                ->getMock()
            ->shouldReceive('run')
                ->once()
                ->with($actionName)
                ->getMock();

        $this->pimpleContainer[$controllerName] = $controllerMock;
    }

    protected function expectResponseSent()
    {
        $this->response
            ->shouldReceive('render')
                ->once()
                ->getMock()
            ->shouldReceive('send')
                ->once();
    }

    protected function expectErrorSentToOutput()
    {
        $this->response
            ->shouldReceive('sendError')
            ->once();
    }
}
