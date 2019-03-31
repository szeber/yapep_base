<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response\Entity;

use YapepBase\Helper\DateHelper;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;

class CookieTest extends TestAbstract
{
    /** @var int */
    protected $currentTime = 1;
    /** @var string */
    protected $name  = 'name';
    /** @var string */
    protected $value = 'value';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend_shouldSendOutCookie()
    {
        $ttlInSeconds = 1;
        $path         = '/test';
        $domain       = '.dev';
        $secure       = true;
        $httpOnly     = true;

        $cookie = new Cookie($this->name, $this->value, $ttlInSeconds, $path, $domain, $secure, $httpOnly, $this->getDateHelperMock());

        $headers = $this->getHeadersAfterMethodCall(function () use ($cookie) {
            $cookie->send();
        });

        $expectedCookieHeader = 'Set-Cookie: name=value; expires=Thu, 01-Jan-1970 00:00:02 GMT; Max-Age=0; path=/test; domain=.dev; secure; HttpOnly';

        $this->assertCount(1, $headers);
        $this->assertSame($expectedCookieHeader, $headers[0]);
    }

    public function cookieToHeaderProvider(): array
    {
        return [
            [
                new Cookie($this->name, $this->value),
                new Header('Set-Cookie', 'name=value; path=/'),
            ],
            [
                new Cookie($this->name, $this->value, 1, '/', '', false, false, $this->getDateHelperMock()),
                new Header('Set-Cookie', 'name=value; expires=Thu, 01-Jan-1970 00:00:02 GMT; Max-Age=1; path=/'),
            ],
            [
                new Cookie($this->name, $this->value, 0, '/', '.dev'),
                new Header('Set-Cookie', 'name=value; path=/; domain=.dev'),
            ],
            [
                new Cookie($this->name, $this->value, 0, '/', '', true),
                new Header('Set-Cookie', 'name=value; path=/; secure'),
            ],
            [
                new Cookie($this->name, $this->value, 0, '/', '', false, true),
                new Header('Set-Cookie', 'name=value; path=/; HttpOnly'),
            ],
            [
                new Cookie($this->name, $this->value, 1, '/path', '.dev', true, true, $this->getDateHelperMock()),
                new Header('Set-Cookie', 'name=value; expires=Thu, 01-Jan-1970 00:00:02 GMT; Max-Age=1; path=/path; domain=.dev; secure; HttpOnly'),
            ],
        ];
    }

    /**
     * @dataProvider cookieToHeaderProvider
     */
    public function testToHeader_shouldReturnProperHeader(Cookie $cookie, Header $expectedHeader)
    {
        $header = $cookie->toHeader();

        $this->assertSame((string)$expectedHeader, (string)$header);
    }

    protected function getDateHelperMock(): DateHelper
    {
        return \Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestamp')
            ->andReturn($this->currentTime)
            ->getMock();
    }
}
