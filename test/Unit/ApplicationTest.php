<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Application;
use YapepBase\Controller\IController;
use YapepBase\Event\Entity\Event;
use YapepBase\Event\IEventHandlerRegistry;
use YapepBase\Exception\Exception;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\Router\Entity\ControllerAction;
use YapepBase\Router\IRouter;

class ApplicationTest extends TestAbstract
{
    /** @var Application */
    private $application;
    /** @var IEventHandlerRegistry|MockInterface */
    private $eventHandlerRegistry;

    /** @var string */
    private $errorControllerName = 'ErrorController';
    /** @var string */
    private $controllerName = 'TestController';
    /** @var string */
    private $actionName = 'testAction';

    /** @var IRequest|MockInterface */
    private $request;
    /** @var IResponse|MockInterface */
    private $response;
    /** @var IRouter|MockInterface */
    private $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = Application::getInstance();
        $this->registerEventHandler();
        $this->registerRequestAndResponse();
    }

    private function registerEventHandler()
    {
        $this->eventHandlerRegistry = Mockery::mock(IEventHandlerRegistry::class)
            ->shouldReceive('isRaised')
            ->zeroOrMoreTimes()
            ->andReturn(true)
            ->getMock();
        $this->pimpleContainer->setEventHandlerRegistry($this->eventHandlerRegistry);
    }

    private function registerRequestAndResponse()
    {
        $this->request  = Mockery::mock(IRequest::class);
        $this->response = Mockery::mock(IResponse::class);
        $this->router   = Mockery::mock(IRouter::class);

        $this->pimpleContainer
            ->setRequest($this->request)
            ->setResponse($this->response)
            ->setRouter($this->router);
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

    private function setErrorController()
    {
        $this->application->setErrorController($this->errorControllerName);
    }

    private function expectAllEventsRaised()
    {
        $this->expectEventRaised(Event::APPLICATION_STARTED);
        $this->expectEventRaised(Event::APPLICATION_CONTROLLER_BEFORE_RUN);
        $this->expectEventRaised(Event::APPLICATION_CONTROLLER_FINISHED);
        $this->expectEventRaised(Event::APPLICATION_OUTPUT_BEFORE_SEND);
        $this->expectEventRaised(Event::APPLICATION_OUTPUT_SENT);
        $this->expectEventRaised(Event::APPLICATION_FINISHED);
    }

    private function expectEventRaised(string $expectedType)
    {
        $event = new Event($expectedType);

        $this->eventHandlerRegistry
            ->shouldReceive('raise')
            ->with(Mockery::on(function () use ($expectedType, $event) {
                return $expectedType === $event->getName();
            }));
    }

    private function expectGetRoute(): void
    {
        $controllerAction = new ControllerAction($this->controllerName, $this->actionName, [], []);
        $this->router
            ->shouldReceive('getControllerActionByRequest')
            ->once()
            ->with($this->request)
            ->andReturn($controllerAction);
    }

    private function expectGetRouteThrowsException(\Exception $expectedException)
    {
        $this->router
            ->shouldReceive('getControllerActionByRequest')
            ->once()
            ->with($this->request)
            ->andThrows($expectedException);
    }

    private function expectRunActionOnController(string $controllerName, string $actionName)
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

    private function expectResponseSent()
    {
        $this->response
            ->shouldReceive('render')
                ->once()
                ->getMock()
            ->shouldReceive('send')
                ->once();
    }

    private function expectErrorSentToOutput()
    {
        $this->response
            ->shouldReceive('sendError')
            ->once();
    }
}
