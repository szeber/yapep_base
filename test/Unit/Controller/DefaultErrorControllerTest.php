<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller;

use Mockery\MockInterface;
use PHPUnit\Framework\Error\Warning;
use YapepBase\Controller\DefaultErrorController;
use YapepBase\Request\Request;
use YapepBase\Response\Response;
use YapepBase\Response\IOutputHandler;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Data\SimpleData;
use YapepBase\View\IRenderable;
use YapepBase\View\Template\SimpleHtmlTemplate;

class DefaultErrorControllerTest extends TestAbstract
{
    /** @var MockInterface|Request */
    protected $request;
    /** @var MockInterface|Response */
    protected $response;
    /** @var MockInterface|IOutputHandler */
    protected $outputHandler;
    /** @var DefaultErrorController */
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputHandler = \Mockery::mock(IOutputHandler::class);
        $this->request       = \Mockery::mock(Request::class);
        $this->response      = \Mockery::mock(Response::class)
            ->shouldReceive('getOutputHandler')
            ->andReturn($this->outputHandler)
            ->getMock();

        $this->controller = new DefaultErrorController();
        $this->controller
            ->setRequest($this->request)
            ->setResponse($this->response);
    }

    public function testDo404_shouldReturn404Page()
    {
        $expectedResponse = $this->getExpectedResponse('<h1>404 - Page not found</h1>');

        $this->expectStatusCodeSet(404);
        $this->expectOutputCleared();
        $this->expectResponseBodySet($expectedResponse);
        $this->controller->run('404');
    }

    public function testDo500_shouldReturn404Page()
    {
        $this->expect500Runs();
        $this->controller->run('500');
    }

    public function testRunWhenActionNotFound_shouldTriggerError()
    {
        $this->expectStatusCodeSet(101);
        $this->expectException(Warning::class);

        $this->controller->run('101');
    }

    public function testRunWhenActionNotFound_shouldRun500()
    {
        $this->expectStatusCodeSet(101);
        $this->expect500Runs();

        @$this->controller->run('101');
    }

    protected function expectStatusCodeSet(int $statusCode)
    {
        $this->response
            ->shouldReceive('setStatusCode')
            ->once()
            ->with($statusCode);
    }

    protected function expectResponseBodySet(IRenderable $expectedBody)
    {
        $this->response
            ->shouldReceive('setBody')
            ->once()
            ->with(\Mockery::on(function (IRenderable $body) use ($expectedBody) {
                return (string)$body === (string)$expectedBody;
            }));
    }

    protected function expectOutputCleared()
    {
        $this->outputHandler
            ->shouldReceive('clear')
            ->once();
    }

    protected function getExpectedResponse(string $message): IRenderable
    {
        return new SimpleHtmlTemplate(new SimpleData($message));
    }

    protected function expect500Runs()
    {
        $expectedResponse = $this->getExpectedResponse('<h1>500 - Internal server error</h1>');

        $this->expectStatusCodeSet(500);
        $this->expectOutputCleared();
        $this->expectResponseBodySet($expectedResponse);
    }
}
