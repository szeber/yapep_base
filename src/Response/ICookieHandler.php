<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Entity\Cookie;

interface ICookieHandler
{
    /**
     * Adds the given Cookie in buffering mode, sends them otherwise.
     */
    public function addCookie(Cookie $cookie): void;

    /**
     * Sends out the stored/buffered cookies
     *
     * Does not do much when not buffering as the headers will be already sent.
     */
    public function sendBufferedCookies(): void;

    /**
     * Checks whether the given cookie is set
     */
    public function hasCookie(string $cookieName): bool;

    /**
     * Removes all the Cookies
     *
     * Only has an effect on the buffered Cookies
     */
    public function clearCookies(): void;
}
