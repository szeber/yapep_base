<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response\Entity;

use YapepBase\Response\Entity\Cookie;

class CookieTest extends TestAbstract
{
    /**
     * @runInSeparateProcess
     */
    public function testSend_shouldSendOutCookie()
    {
        $name         = 'name';
        $value        = 'value';
        $ttlInSeconds = 1;
        $path         = '/test';
        $domain       = '.dev';
        $secure       = true;
        $httpOnly     = true;

        $cookie = new Cookie($name, $value, $ttlInSeconds, $path, $domain, $secure, $httpOnly);

        $headers = $this->getHeadersAfterMethodCall(function () use ($cookie) {$cookie->send();});

        $expectedCookieHeader = 'Set-Cookie: name=value; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/test; domain=.dev; secure; HttpOnly';

        $this->assertCount(1, $headers);
        $this->assertSame($expectedCookieHeader, $headers[0]);
    }

}
