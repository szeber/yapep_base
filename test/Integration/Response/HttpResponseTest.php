<?php
declare(strict_types=1);

namespace YapepBase\Test\Integration\Response;

use Lukasoppermann\Httpstatus\Httpstatus;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\OutputBufferHandler;
use YapepBase\Response\OutputHandler;
use YapepBase\Test\Integration\TestAbstract;
use YapepBase\View\IRenderable;

class HttpResponseTest extends TestAbstract
{
    /** @var string */
    protected $renderedBody = 'renderedBody';
    /** @var string */
    protected $echoedContent = 'echoedContent';
    /** @var int */
    protected $originalObLevel;
    /** @var Header */
    protected $header;
    /** @var Header */
    protected $expectedContentTypeHeader;
    /** @var Cookie */
    protected $cookie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalObLevel = ob_get_level();
        $this->initHeaders();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        while (ob_get_level() > $this->originalObLevel) {
            ob_get_clean();
        }
    }

    protected function initHeaders()
    {
        $this->header                    = new Header('Header1', '1');
        $this->expectedContentTypeHeader = new Header('Content-Type', 'text/html; charset=UTF-8');
        $this->cookie                    = new Cookie('Cookie1', '3');
    }

    protected function getHttpResponseBuffered(): HttpResponse
    {
        $bufferHandler = new OutputBufferHandler();
        $statusHelper  = new Httpstatus();
        $outputHandler = new OutputHandler(false, $bufferHandler);

        return new HttpResponse($outputHandler, $statusHelper);
    }

    protected function getHttpResponse(): HttpResponse
    {
        $statusHelper  = new Httpstatus();
        $outputHandler = new OutputHandler(false);

        return new HttpResponse($outputHandler, $statusHelper);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendWhenBuffering_shouldBufferAndSendOutBodyOnlyWhenSendCalled()
    {
        $httpResponse = $this->getHttpResponseBuffered();
        $httpResponse->setBody($this->getView());

        echo $this->echoedContent;

        $httpResponse->render();

        $this->expectOutputString($this->renderedBody . $this->echoedContent);
        $httpResponse->send();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendWhenBuffering_shouldBufferAndSendOutHeadersOnlyWhenSendCalled()
    {
        $httpResponse = $this->getHttpResponseBuffered();

        $httpResponse->sendHeader($this->header);
        $httpResponse->sendCookie($this->cookie);

        $httpResponse->render();
        $httpResponse->send();
        $headers = xdebug_get_headers();

        $this->assertCount(3, $headers);
        $this->assertContains((string)$this->header, $headers);
        $this->assertContains((string)$this->expectedContentTypeHeader, $headers);
        $this->assertContains((string)$this->cookie->toHeader(), $headers);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendWhenNoBuffering_shouldLetDirectOutput()
    {
        $httpResponse = $this->getHttpResponse();

        $this->expectOutputString($this->echoedContent);
        echo $this->echoedContent;
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendWhenNoBuffering_shouldSendOutHeadersImmediately()
    {
        $httpResponse = $this->getHttpResponse();

        $httpResponse->sendHeader($this->header);

        $headers = xdebug_get_headers();
        $this->assertCount(1, $headers);
        $this->assertContains((string)$this->header, $headers);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendWhenNoBuffering_shouldSendOutCookiesImmediately()
    {
        $httpResponse = $this->getHttpResponse();

        $httpResponse->sendCookie($this->cookie);

        $headers = xdebug_get_headers();

        $this->assertCount(1, $headers);
        $this->assertContains((string)$this->cookie->toHeader(), $headers);
    }

    protected function getView(): IRenderable
    {
        return \Mockery::mock(IRenderable::class)
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($this->renderedBody)
            ->getMock();
    }
}
