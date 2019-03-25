<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response;

use Mockery\MockInterface;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Exception\Exception;
use YapepBase\Response\OutputBufferHandler;
use YapepBase\Response\OutputHandler;
use YapepBase\Test\Unit\TestAbstract;

class OutputHandlerTest extends TestAbstract
{
    /** @var string */
    protected $headerName = 'header1';
    /** @var string */
    protected $headerValue = 'value1';
    /** @var string */
    protected $content = 'content';
    /** @var MockInterface */
    protected $outputBufferHandler;
    /** @var string */
    protected $cookieName = 'cookie1';

    public function setUp(): void
    {
        parent::setUp();
        $this->outputBufferHandler = \Mockery::mock(OutputBufferHandler::class);
    }

    public function testConstructWhenBufferNotGiven_shouldNotStartBuffering()
    {
        $outputHandler = new OutputHandler(false);

        $isBuffering = $outputHandler->isBuffering();

        $this->assertFalse($isBuffering);
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructWhenBufferGiven_shouldStartBuffering()
    {
        $this->expectBufferingStarted();
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $isBuffering = $outputHandler->isBuffering();

        $this->assertTrue($isBuffering);
    }


    public function testStartBufferHeadersAlreadySent_shouldThrowException()
    {
        $outputHandler = new OutputHandler(false);

        $this->expectException(Exception::class);
        $outputHandler->startBuffer($this->outputBufferHandler);
    }

    public function testStopBufferWhenNotBuffering_shouldDoNothing()
    {
        $outputHandler = new OutputHandler(false);

        $outputHandler->stopBuffer();

        // Lame but we need this to avoid Risky flag from PHPUnit
        $this->assertNull(null);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStopBufferWhenBuffering_shouldSendContent()
    {
        $this->expectBufferingStarted();
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $this->expectBufferingStopped($this->content);

        $this->expectOutputString($this->content);
        $outputHandler->stopBuffer();
    }

    /**
     * @runInSeparateProcess
     */
    public function testStopBufferWhenBuffering_shouldSendHeaders()
    {
        $header = $this->expectHeaderSent();
        $this->expectBufferingStarted();
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);
        $outputHandler->addHeader($header);

        $this->expectBufferingStopped();

        $outputHandler->stopBuffer();

        $this->assertFalse($outputHandler->isBuffering());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendHeadersWhenObIsOff_shouldNotSendHeadersAgain()
    {
        $header        = $this->expectHeaderSent();
        $outputHandler = new OutputHandler(false);

        $outputHandler->addHeader($header);

        $outputHandler->sendBufferedHeaders();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendHeadersWhenObIsOn_shouldSendHeaders()
    {
        $header = $this->expectHeaderSent();
        $this->expectBufferingStarted();
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $outputHandler->addHeader($header);

        $outputHandler->sendBufferedHeaders();
    }

    public function testSendBufferedCookies_shouldSendGivenCookies()
    {
        $outputHandler = new OutputHandler(false);
        $outputHandler->addCookie($this->expectCookieSent());
        $outputHandler->addCookie($this->expectCookieSent());

        $outputHandler->sendBufferedCookies();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendContent_shouldEchoGivenContent()
    {
        $outputHandler = new OutputHandler(false);

        $this->expectOutputString($this->content);
        $outputHandler->sendContent($this->content);
    }

    public function testAddHeaderWhenObIsOff_shouldSendHeader()
    {
        $header        = $this->expectHeaderSent();
        $outputHandler = new OutputHandler(false);

        $outputHandler->addHeader($header);

        $headerSet = $outputHandler->hasHeader($this->headerName);

        $this->assertTrue($headerSet);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddHeaderWhenHeaderNotExistYet_shouldAdd()
    {
        $this->expectBufferingStarted();
        $header        = new Header($this->headerName, $this->headerValue);
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $outputHandler->addHeader($header);

        $headerSet = $outputHandler->hasHeader($this->headerName);

        $this->assertTrue($headerSet);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddHeaderWhenHeaderExists_shouldMerge()
    {
        $this->expectBufferingStarted();
        $header1       = new Header($this->headerName, $this->headerValue);
        $header2       = new Header($this->headerName, 'another');
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $outputHandler->addHeader($header1);
        $outputHandler->addHeader($header2);

        $headers = $outputHandler->getHeaders($this->headerName);

        $this->assertSame([$header1, $header2], $headers);
    }

    public function testHasHeaderWhenHeaderNotExist_shouldReturnFalse()
    {
        $outputHandler = new OutputHandler(false);

        $headerSet = $outputHandler->hasHeader($this->headerName);

        $this->assertFalse($headerSet);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHasHeaderWhenHeaderExist_shouldReturnTrue()
    {
        $this->expectBufferingStarted();
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);
        $outputHandler->addHeader(new Header($this->headerName));

        $headerSet = $outputHandler->hasHeader($this->headerName);

        $this->assertTrue($headerSet);
    }

    public function testGetBufferedContentWhenObIsOff_shouldReturnEmptyString()
    {
        $outputHandler = new OutputHandler(false);

        $result = $outputHandler->getBufferedContent();

        $this->assertEmpty($result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetBufferedContentWhenObIsOn_shouldReturnBufferedContent()
    {
        $this->expectBufferingStarted();
        $this->expectBufferingStopped($this->content);
        $outputHandler = new OutputHandler(false, $this->outputBufferHandler);

        $result = $outputHandler->getBufferedContent();

        $this->assertEquals($this->content, $result);
    }

    protected function expectBufferingStarted(): void
    {
        $this->outputBufferHandler
            ->shouldNotReceive('start')
            ->once();
    }

    protected function expectBufferingStopped(string $result = ''): void
    {
        $this->outputBufferHandler
            ->shouldNotReceive('stop')
            ->once()
            ->andReturn($result);
    }

    protected function expectHeaderSent(): Header
    {
        return \Mockery::mock(Header::class)
            ->shouldReceive('getName')
                ->andReturn($this->headerName)
                ->getMock()
            ->shouldReceive('getValue')
                ->andReturn($this->headerValue)
                ->getMock()
            ->shouldReceive('send')
                ->once()
                ->getMock();
    }

    protected function expectCookieSent(): Cookie
    {
        return \Mockery::mock(Cookie::class)
            ->shouldReceive('getName')
                ->andReturn($this->cookieName)
                ->getMock()
            ->shouldReceive('send')
                ->once()
                ->getMock();
    }
}
