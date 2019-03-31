<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Entity\Cookie;

/**
 * Responsible for creating Cookies
 *
 * The purpose of this factory is to be able to handle the cookies the same way everywhere in the project in a preconfigured way.
 * For example if one need to use https cookies on production but only http on Development environment.
 *
 * Just set the Factory to the DI Container and use it everywhere.
 */
class CookieFactory
{
    /** @var bool */
    protected $secure;

    /** @var bool */
    protected $httpOnly;

    public function __construct(bool $secure, bool $httpOnly)
    {
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function getCookie(
        string $name,
        string $value,
        int $ttlInSeconds = 0,
        string $path = '/',
        string $domain = ''
    ): Cookie {
        return new Cookie(
            $name,
            $value,
            $ttlInSeconds,
            $path,
            $domain,
            $this->secure,
            $this->httpOnly
        );
    }
}
