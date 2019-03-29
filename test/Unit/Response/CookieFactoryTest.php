<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response;

use YapepBase\Response\CookieFactory;
use YapepBase\Test\Unit\TestAbstract;

class CookieFactoryTest extends TestAbstract
{
    public function cookieFlagProvider(): array
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    /**
     * @dataProvider cookieFlagProvider
     */
    public function testGetCookie_shouldSetSecureAndHttpFlagsAccordingToGivenValues(bool $secure, bool $httpOnly)
    {
        $cookieFactory = new CookieFactory($secure, $httpOnly);

        $cookie = $cookieFactory->getCookie('name', 'value');

        $this->assertSame($secure, $cookie->isSecure());
        $this->assertSame($httpOnly, $cookie->isHttpOnly());
    }
}
