<?php
declare(strict_types=1);

namespace YapepBase\Response;

use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes;
use YapepBase\Exception\RedirectException;
use YapepBase\Mime\MimeType;
use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Exception\Exception;
use YapepBase\Response\Exception\ResponseSentException;
use YapepBase\View\IRenderable;
use YapepBase\View\ViewAbstract;

/**
 * Class responsible for handling a HTTP response
 */
class HttpResponse implements IResponse, IHttpResponse
{
    const STATUS_MESSAGE_NON_STANDARD = 'Non-Standard Response';

    /** @var IOutputHandler */
    protected $outputHandler;

    /** @var Httpstatus */
    protected $statusHelper;

    /** @var string */
    protected $charset = 'UTF-8';

    /** @var ViewAbstract|null */
    protected $body;

    /** @var string|null */
    protected $renderedBody;

    /** @var int */
    protected $statusCode = Httpstatuscodes::HTTP_OK;

    /** @var string */
    protected $statusMessage = 'OK';

    /** @var string */
    protected $contentType;

    /** @var bool */
    protected $isSent = false;

    public function __construct(IOutputHandler $outputHandler, Httpstatus $statusHelper)
    {
        $this->outputHandler = $outputHandler;
        $this->statusHelper  = $statusHelper;
        $this->setContentType(MimeType::HTML);
    }

    public function sendHeader(Header $header): void
    {
        $this->outputHandler->addHeader($header);
    }

    public function sendCookie(Cookie $cookie): void
    {
        $this->outputHandler->addCookie($cookie);
    }

    public function setBody(IRenderable $body): void
    {
        if (!empty($this->renderedBody)) {
            throw new Exception('Rendered body already set');
        }
        $this->body = $body;
    }

    public function setRenderedBody(string $body): void
    {
        if (!empty($this->body)) {
            throw new Exception('Body already set');
        }
        $this->renderedBody = $body;
    }

    public function getBody(): ?IRenderable
    {
        return $this->body;
    }

    public function getRenderedBody(): ?string
    {
        return $this->renderedBody;
    }

    public function render(): void
    {
        if (!empty($this->renderedBody)) {
            return;
        }

        $this->renderedBody = (string)$this->body;
    }

    public function send(): void
    {
        if ($this->isSent) {
            throw new ResponseSentException();
        }

        $this->isSent = true;

        $this->sendStatusCodeHeader();
        $this->sendContentTypeHeader();

        $this->outputHandler->sendBufferedHeaders();
        $this->outputHandler->sendBufferedCookies();

        $bufferedContent = $this->outputHandler->getBufferedContent();

        $this->outputHandler->sendContent($this->renderedBody);
        $this->outputHandler->sendContent($bufferedContent);
    }

    public function sendError(): void
    {
        if ($this->isSent) {
            throw new ResponseSentException();
        }

        $this->isSent = true;
        $this->sendInternalError();
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function setStatusCode(int $statusCode, string $statusMessage = ''): void
    {
        if (!empty($statusCode)) {
            if ($this->statusHelper->hasStatusCode($statusCode)) {
                $statusMessage = $this->statusHelper->getReasonPhrase($statusCode);
            } else {
                $statusMessage = self::STATUS_MESSAGE_NON_STANDARD;
            }
        }
        $this->statusCode    = $statusCode;
        $this->statusMessage = $statusMessage;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function redirect(string $url, int $statusCode = 303): void
    {
        $this->setStatusCode($statusCode);
        $this->outputHandler->setHeader(new Header('Location', $url));

        throw new RedirectException($url, RedirectException::TYPE_EXTERNAL);
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getOutputHandler(): IOutputHandler
    {
        return $this->outputHandler;
    }

    private function sendStatusCodeHeader(): void
    {
        $statusCodeHeader = 'HTTP/1.1 ' . $this->statusCode . ' ' . $this->statusMessage;
        $this->sendHeader(new Header($statusCodeHeader));
    }

    private function sendContentTypeHeader(): void
    {
        $this->sendHeader(new Header('Content-Type', $this->contentType . '; charset=' . $this->charset));
    }

    private function sendInternalError(): void
    {
        $internalErrorHeader = new Header('HTTP/1.1 500 Internal Server Error');

        $this->outputHandler->setHeader($internalErrorHeader);
        $this->outputHandler->sendContent('<h1>Internal server error</h1>');
    }
}
