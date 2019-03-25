<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response;

use Lukasoppermann\Httpstatus\Httpstatus;
use Mockery\MockInterface;
use YapepBase\Exception\RedirectException;
use YapepBase\Mime\MimeType;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Exception\Exception;
use YapepBase\Response\Exception\ResponseSentException;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\IOutputHandler;
use YapepBase\Test\Unit\Response\Entity\TestAbstract;
use YapepBase\View\ViewAbstract;

class HttpResponseTest extends TestAbstract
{
    /** @var HttpResponse */
    protected $httpResponse;
    /** @var MockInterface */
    protected $outputHandler;
    /** @var MockInterface */
    protected $statusHelper;
    /** @var MockInterface */
    protected $view;
    /** @var string */
    protected $renderedBody = 'rendered';
    /** @var string */
    protected $bufferedContent = 'buffered';
    /** @var int */
    protected $statusCode = 200;
    /** @var string */
    protected $statusMessage = 'OK';

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputHandler = \Mockery::mock(IOutputHandler::class);
        $this->statusHelper  = \Mockery::mock(Httpstatus::class);
        $this->httpResponse  = new HttpResponse($this->outputHandler, $this->statusHelper);
        $this->view          = \Mockery::mock(ViewAbstract::class);
    }

    public function testConstruct_shouldSetContentTypeToHtml()
    {
        $expectedContentType = MimeType::HTML;

        $contentType = $this->httpResponse->getContentType();

        $this->assertSame($expectedContentType, $contentType);
    }

    public function testSendHeader_shouldAddHeaderToOutputHandler()
    {
        $header = new Header('header', 'value');

        $this->expectHeaderAdded($header);
        $this->httpResponse->sendHeader($header);
    }

    public function testSendCookie_shouldAddCookieToOutputHandler()
    {
        $cookie = new Cookie('cookie', 'value');

        $this->expectCookieAdded($cookie);
        $this->httpResponse->sendCookie($cookie);
    }

    public function testSetBodyWhenRenderedBodyAlreadySet_shouldThrowException()
    {
        $this->httpResponse->setRenderedBody($this->renderedBody);

        $this->expectException(Exception::class);
        $this->httpResponse->setBody($this->view);
    }

    public function testSetRenderedBodyWhenBodyAlreadySet_shouldThrowException()
    {
        $this->httpResponse->setBody($this->view);

        $this->expectException(Exception::class);
        $this->httpResponse->setRenderedBody($this->renderedBody);
    }

    public function testRenderWhenRenderedBodyAlreadySet_shouldDoNothing()
    {
        $this->httpResponse->setRenderedBody($this->renderedBody);
        $this->httpResponse->render();

        $this->assertSame($this->renderedBody, $this->httpResponse->getRenderedBody());
    }

    public function testRender_shouldRenderBody()
    {
        $this->httpResponse->setBody($this->view);
        $this->expectViewRendered();

        $this->httpResponse->render();

        $this->assertSame($this->renderedBody, $this->httpResponse->getRenderedBody());
    }

    public function testSend_shouldSendOutHeadersCookiesAndContent()
    {
        $this->expectSendCalled();

        $this->httpResponse->send();
        $this->assertTrue($this->httpResponse->isSent());
    }

    public function testSendWhenAlreadySent_shouldThrowException()
    {
        $this->expectSendCalled();
        $this->httpResponse->send();

        $this->expectException(ResponseSentException::class);
        $this->httpResponse->send();
    }

    public function testSendError_shouldSendInternalError()
    {
        $this->expectInternalErrorSent();

        $this->httpResponse->sendError();
        $this->assertTrue($this->httpResponse->isSent());
    }

    public function testSendErrorWhenErrorAlreadySent_shouldThrowException()
    {
        $this->expectInternalErrorSent();
        $this->httpResponse->sendError();

        $this->expectException(ResponseSentException::class);
        $this->httpResponse->sendError();
    }

    public function testSendErrorWhenResponseAlreadySent_shouldThrowException()
    {
        $this->expectSendCalled();
        $this->httpResponse->send();

        $this->expectException(ResponseSentException::class);
        $this->httpResponse->sendError();
    }

    public function testSetStatusCodeWhenGivenCodeIsEmpty_shouldJustStore()
    {
        $statusCode    = 0;
        $this->httpResponse->setStatusCode($statusCode, $this->statusMessage);

        $this->assertSame($statusCode, $this->httpResponse->getStatusCode());
        $this->assertSame($this->statusMessage, $this->httpResponse->getStatusMessage());
    }

    public function testSetStatusCodeWhenGivenCodeRecognised_shouldStoreMessageAccordingToIt()
    {
        $expectedStatusMessage = 'Another message';

        $this->expectStatusCodeChecked(true);
        $this->expectStatusMessageRetrieved($expectedStatusMessage);

        $this->httpResponse->setStatusCode($this->statusCode, $this->statusMessage);

        $this->assertSame($this->statusCode, $this->httpResponse->getStatusCode());
        $this->assertSame($expectedStatusMessage, $this->httpResponse->getStatusMessage());
    }

    public function testSetStatusCodeWhenGivenCodeNotRecognised_shouldStoreDefaultMessage()
    {
        $this->expectStatusCodeChecked(false);

        $this->httpResponse->setStatusCode($this->statusCode, $this->statusMessage);

        $this->assertSame($this->statusCode, $this->httpResponse->getStatusCode());
        $this->assertSame(HttpResponse::STATUS_MESSAGE_NON_STANDARD, $this->httpResponse->getStatusMessage());
    }

    public function testRedirect_shouldThrowRedirectException()
    {
        $url        = '/test';
        $statusCode = 0;
        $locationHeader = new Header('Location', $url);

        $this->expectHeaderSet($locationHeader);
        $this->expectException(RedirectException::class);
        $this->expectExceptionCode(RedirectException::TYPE_EXTERNAL);
        $this->expectExceptionMessage($url);

        $this->httpResponse->redirect($url, $statusCode);
    }

    protected function expectHeaderAdded(Header $expectedHeader): void
    {
        $this->outputHandler
            ->shouldReceive('addHeader')
            ->once()
            ->with(\Mockery::on(function (Header $header) use ($expectedHeader) {
                return $header->getName() === $expectedHeader->getName()
                    && $header->getValue() === $expectedHeader->getValue();
            }));
    }

    protected function expectHeaderSet(Header $expectedHeader): void
    {
        $this->outputHandler
            ->shouldReceive('setHeader')
            ->once()
            ->with(\Mockery::on(function (Header $header) use ($expectedHeader) {
                return $header->getName() === $expectedHeader->getName()
                    && $header->getValue() === $expectedHeader->getValue();
            }));
    }

    protected function expectCookieAdded(Cookie $expectedCookie): void
    {
        $this->outputHandler
            ->shouldReceive('addCookie')
            ->once()
            ->with(\Mockery::on(function (Cookie $cookie) use ($expectedCookie) {
                return $cookie->getName() === $expectedCookie->getName()
                    && $cookie->getValue() === $expectedCookie->getValue();
            }));
    }

    protected function expectViewRendered(): void
    {
        $this->view
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($this->renderedBody);
    }

    protected function expectSendCalled(): void
    {
        $this->expectStatusCodeHeaderSent();
        $this->expectContentTypeHeaderSent();
        $this->expectContentSent();
        $this->httpResponse->setRenderedBody($this->renderedBody);
    }

    protected function expectStatusCodeHeaderSent(): void
    {
        $statusCodeHeader = new Header('HTTP/1.1 200 OK');
        $this->expectHeaderAdded($statusCodeHeader);
    }

    protected function expectContentTypeHeaderSent(): void
    {
        $contentTypeHeader = new Header('Content-Type', 'text/html; charset=UTF-8');
        $this->expectHeaderAdded($contentTypeHeader);
    }

    protected function expectContentSent(): void
    {
        $this->outputHandler
            ->shouldReceive('sendBufferedHeaders')
                ->once()
                ->getMock()
            ->shouldReceive('sendBufferedCookies')
                ->once()
                ->getMock()
            ->shouldReceive('getBufferedContent')
                ->once()
                ->andReturn($this->bufferedContent);

        $this->expectSendContentCalled($this->renderedBody);
        $this->expectSendContentCalled($this->bufferedContent);
    }

    protected function expectSendContentCalled(string $expectedContent): void
    {
        $this->outputHandler
            ->shouldReceive('sendContent')
            ->once()
            ->with($expectedContent);
    }

    protected function expectInternalErrorSent(): void
    {
        $statusCodeHeader = new Header('HTTP/1.1 500 Internal Server Error');
        $this->expectHeaderSet($statusCodeHeader);
        $this->expectSendContentCalled('<h1>Internal server error</h1>');
    }

    protected function expectStatusCodeChecked(bool $expectedResult)
    {
        $this->statusHelper
            ->shouldReceive('hasStatusCode')
            ->once()
            ->andReturn($expectedResult);
    }

    protected function expectStatusMessageRetrieved(string $expectedResult)
    {
        $this->statusHelper
            ->shouldReceive('getReasonPhrase')
            ->once()
            ->with($this->statusCode)
            ->andReturn($expectedResult);
    }
}
