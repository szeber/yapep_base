<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Exception\ResponseSentException;
use YapepBase\View\ViewAbstract;

interface IResponse
{
    public function setBody(ViewAbstract $body): void;

    public function setRenderedBody(string $body): void;

    public function getBody(): ?ViewAbstract;

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
