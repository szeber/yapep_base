<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Exception\RedirectException;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Exception\ResponseSentException;
use YapepBase\View\IRenderable;

interface IResponse
{
    public function setBody(IRenderable $body): void;

    public function setRenderedBody(string $body): void;

    public function getBody(): ?IRenderable;

    public function getRenderedBody(): ?string;

    public function render(): void;

    /**
     * @throws ResponseSentException
     */
    public function send(): void;

    /**
     * @throws ResponseSentException
     */
    public function sendError(): void;

    public function isSent(): bool;

    public function getOutputHandler(): IOutputHandler;

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
