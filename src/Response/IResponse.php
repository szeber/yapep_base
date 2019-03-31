<?php
declare(strict_types=1);

namespace YapepBase\Response;

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
}
