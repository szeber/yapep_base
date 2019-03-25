<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Exception\RedirectException;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;

interface IHttpResponse
{
    public function sendHeader(Header $header): void;

    public function sendCookie(Cookie $cookie): void;

    public function setStatusCode(int $statusCode, string $statusMessage = ''): void;

    public function getStatusCode(): int;

    public function getStatusMessage(): string;

    /**
     * @throws RedirectException
     */
    public function redirect(string $url, int $statusCode = 303): void;

    public function setCharset(string $charset): void;

    public function setContentType(string $contentType): void;

    public function getContentType(): ?string;
}
