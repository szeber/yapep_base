<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller\Rest;

use Mockery\MockInterface;
use PHPUnit\Framework\Error\Error;
use YapepBase\Controller\Rest\Entity\RestError;
use YapepBase\Controller\Rest\Exception\Exception;
use YapepBase\Controller\Rest\Exception\ResourceDoesNotExistException;
use YapepBase\Controller\Rest\Exception\UnauthenticatedException;
use YapepBase\Exception\HttpException;
use YapepBase\Exception\RedirectException;
use YapepBase\Mime\MimeType;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\IOutputHandler;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Data\SimpleData;
use YapepBase\View\IRenderable;
use YapepBase\View\Template\JsonTemplate;

class ControllerAbstractTest extends TestAbstract
{
    /** @var ControllerAbstractStub */
    protected $controller;
    /** @var MockInterface|HttpRequest */
    protected $request;
    /** @var MockInterface|HttpResponse */
    protected $response;
    /** @var MockInterface|IOutputHandler */
    protected $outputHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request    = \Mockery::mock(HttpRequest::class);
        $this->response   = \Mockery::mock(HttpResponse::class);
        $this->controller = new ControllerAbstractStub();

        $this->controller->setRequest($this->request);
        $this->controller->setResponse($this->response);
        $this->expectJsonContentTypeSet();

        $this->outputHandler = \Mockery::mock(IOutputHandler::class);
        $this->response->shouldReceive('getOutputHandler')->andReturn($this->outputHandler);
    }

    public function methodProvider(): array
    {
        return [
            [HttpRequest::METHOD_HTTP_GET, 'getTest'],
            [HttpRequest::METHOD_HTTP_POST, 'postTest'],
            [HttpRequest::METHOD_HTTP_PUT, 'putTest'],
            [HttpRequest::METHOD_HTTP_DELETE, 'deleteTest'],
        ];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testRunWithDifferentMethods_shouldReturnResponse(string $requestMethod, string $actionMethodName)
    {
        $actionResult = $this->controller->$actionMethodName();

        $this->expectRequestMethod($requestMethod);
        $this->expectBodySetToResponse($actionResult);
        $this->controller->run('test');
    }

    public function testRunWhenActionDoesNotExist_shouldReturnError()
    {
        $method    = HttpRequest::METHOD_HTTP_GET;
        $action    = 'nonExistent';
        $exception = new ResourceDoesNotExistException($method, $action);

        $this->expectRequestMethod($method);
        $this->expectGetStatusCode(404);
        $this->expectSetStatusCode(404);
        $this->expectBodySetToResponse($this->getErrorResponse($exception));
        $this->controller->run($action);
    }

    public function testRunWhenActionDoesNotExistAndOptionCalled_shouldSendAllowHeader()
    {
        $method         = HttpRequest::METHOD_HTTP_OPTIONS;
        $action         = 'test';
        $allowedMethods = [
            HttpRequest::METHOD_HTTP_GET,
            HttpRequest::METHOD_HTTP_POST,
            HttpRequest::METHOD_HTTP_PUT,
            HttpRequest::METHOD_HTTP_DELETE,
        ];

        $this->expectRequestMethod($method);
        $this->expectAllowHeaderSent($allowedMethods);
        $this->controller->run($action);
    }

    public function testRunWhenUnauthenticatedExceptionThrown_shouldSendAuthHeader()
    {
        $this->controller->exception = new UnauthenticatedException();

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectGetStatusCode(200);
        $this->expectSetStatusCode($this->controller->exception->getRecommendedHttpStatusCode());
        $this->expectHeaderExistenceChecked('WWW-Authenticate', false);
        $this->expectAuthHeaderSent();
        $this->expectBodySetToResponse($this->getErrorResponse($this->controller->exception));
        $this->controller->run('exception');
    }

    public function testRunWhenUnauthenticatedExceptionThrownAndAllowHeaderAlreadySent_shouldNotSendAuthHeaderAgain()
    {
        $this->controller->exception = new UnauthenticatedException();

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectGetStatusCode(200);
        $this->expectSetStatusCode($this->controller->exception->getRecommendedHttpStatusCode());
        $this->expectHeaderExistenceChecked('WWW-Authenticate', true);
        $this->expectBodySetToResponse($this->getErrorResponse($this->controller->exception));
        $this->controller->run('exception');
    }

    public function testRunWhenResourceDoesNotExistExceptionThrown_shouldSendAuthHeader()
    {
        $method                      = HttpRequest::METHOD_HTTP_GET;
        $action                      = 'exception';
        $this->controller->exception = new ResourceDoesNotExistException($method, $action);

        $this->expectRequestMethod($method);
        $this->expectGetStatusCode(200);
        $this->expectSetStatusCode($this->controller->exception->getRecommendedHttpStatusCode());
        $this->expectHeaderExistenceChecked('Allow', false);
        $this->expectAllowHeaderSent([HttpRequest::METHOD_HTTP_GET]);
        $this->expectBodySetToResponse($this->getErrorResponse($this->controller->exception));
        $this->controller->run($action);
    }

    public function testRunWhenResourceDoesNotExistExceptionThrownAndAllowHeaderAlreadySent_shouldNotSendAuthHeaderAgain()
    {
        $method                      = HttpRequest::METHOD_HTTP_GET;
        $action                      = 'exception';
        $this->controller->exception = new ResourceDoesNotExistException($method, $action);

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectGetStatusCode(200);
        $this->expectSetStatusCode($this->controller->exception->getRecommendedHttpStatusCode());
        $this->expectHeaderExistenceChecked('Allow', true);
        $this->expectBodySetToResponse($this->getErrorResponse($this->controller->exception));
        $this->controller->run($action);
    }

    public function reThrowableExceptionProvider(): array
    {
        return [
            [new HttpException()],
            [new RedirectException('', RedirectException::TYPE_INTERNAL)]
        ];
    }

    /**
     * @dataProvider reThrowableExceptionProvider
     */
    public function testRunWhenHttpOrRedirectExceptionThrown_shouldReThrowThem(\Exception $exception)
    {
        $this->controller->exception = $exception;

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectExceptionObject($exception);
        $this->controller->run('exception');
    }

    public function testRunWhenGeneralExceptionThrown_shouldTriggerError()
    {
        $this->controller->exception = new \Exception();

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectException(Error::class);
        $this->controller->run('exception');
    }

    public function testRunWhenGeneralExceptionThrown_shouldReturn500()
    {
        $this->controller->exception = new \Exception();

        $this->expectRequestMethod(HttpRequest::METHOD_HTTP_GET);
        $this->expectSetStatusCode(500);
        $this->expectBodySetToResponse($this->getErrorResponse(new Exception()));
        @$this->controller->run('exception');
    }

    protected function expectRequestMethod(string $method)
    {
        $this->request
            ->shouldReceive('getMethod')
            ->andReturn($method);
    }

    protected function expectJsonContentTypeSet()
    {
        $this->response
            ->shouldReceive('setContentType')
            ->once()
            ->with(MimeType::JSON);
    }

    protected function expectBodySetToResponse(IRenderable $expectedResult)
    {
        $this->response
            ->shouldReceive('setBody')
            ->once()
            ->with(\Mockery::on(function (IRenderable $actionResult) use ($expectedResult) {
                return (string)$actionResult == (string)$expectedResult;
            }));
    }

    protected function expectGetStatusCode(int $statusCode)
    {
        $this->response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode);
    }

    protected function expectSetStatusCode(int $statusCode)
    {
        $this->response
            ->shouldReceive('setStatusCode')
            ->andReturn($statusCode);
    }

    protected function getErrorResponse(Exception $exception): JsonTemplate
    {
        $error    = new RestError($exception->getCodeString(), $exception->getMessage(), $exception->getRequestParams());
        $viewData = new SimpleData($error->toArray());

        return new JsonTemplate($viewData);
    }

    protected function expectAllowHeaderSent(array $allowedMethods)
    {
        $allowHeader = new Header('Allow', implode(', ', $allowedMethods));
        $this->expectHeaderSent($allowHeader);
    }

    protected function expectAuthHeaderSent()
    {
        $authHeader = new Header('WWW-Authenticate', 'Session realm="Please provide the session token"');
        $this->expectHeaderSent($authHeader);
    }

    protected function expectHeaderSent(Header $expectedHeader)
    {
        $this->response
            ->shouldReceive('sendHeader')
            ->once()
            ->with(\Mockery::on(function (Header $header) use ($expectedHeader) {
                return (string)$header === (string)$expectedHeader;
            }));
    }

    protected function expectHeaderExistenceChecked(string $headerName, bool $expectedResult)
    {
        $this->outputHandler
            ->shouldReceive('hasHeader')
            ->once()
            ->with($headerName)
            ->andReturn($expectedResult);
    }
}
