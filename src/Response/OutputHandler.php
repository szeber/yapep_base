<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Entity\Cookie;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Entity\HeaderContainer;
use YapepBase\Response\Exception\Exception;

/**
 * Default Output handler
 */
class OutputHandler implements IOutputHandler
{
    /** @var bool */
    protected $compressWithGzip = false;

    /** @var OutputBufferHandler */
    protected $bufferHandler;

    /** @var int|null */
    protected $startingObLevel;

    /** @var HeaderContainer[] */
    protected $headersByName = [];

    /** @var Cookie[] */
    protected $cookiesByName = [];

    public function __construct(bool $compressWithGzip, ?OutputBufferHandler $bufferHandler = null)
    {
        $this->compressWithGzip = $compressWithGzip;

        if (!empty($bufferHandler)) {
            $this->startBuffer($bufferHandler);
        }
    }

    public function isBuffering(): bool
    {
        return !empty($this->bufferHandler);
    }

    public function startBuffer(OutputBufferHandler $bufferHandler): void
    {
        if (headers_sent()) {
            throw new Exception('Headers already sent!');
        }

        $this->bufferHandler = $bufferHandler;
        $this->bufferHandler->start();
    }

    public function stopBuffer(): void
    {
        if (!$this->isBuffering()) {
            return;
        }

        $this->sendBufferedHeaders();
        $this->sendBufferedCookies();
        echo $this->bufferHandler->stop();

        $this->bufferHandler = null;
    }

    public function addHeader(Header $header): void
    {
        $headerName = $header->getName();

        if (!$this->hasHeader($headerName)) {
            $headerContainer = new HeaderContainer($headerName);
            $headerContainer->add($header);

            $this->headersByName[$headerName] = $headerContainer;
        }
        else {
            $this->headersByName[$headerName]->add($header);
        }

        if (!$this->isBuffering()) {
            $header->send();
        }
    }

    public function removeHeader(Header $header): void
    {
        $headerName = $header->getName();

        if (!$this->hasHeader($headerName)) {
            return;
        }

        $this->headersByName[$headerName]->remove($header);
        header_remove($headerName);
    }

    public function clearHeadersByName(string $headerName): void
    {
        if (!$this->hasHeader($headerName)) {
            return;
        }

        header_remove($headerName);
        unset($this->headersByName[$headerName]);
    }

    public function setHeader(Header $header): void
    {
        $headerName = $header->getName();
        $this->clearHeadersByName($headerName);
        $this->addHeader($header);
    }


    public function getHeaders(string $headerName): array
    {
        return array_values($this->headersByName[$headerName]->toArray());
    }

    public function hasHeader(string $headerName): bool
    {
        return isset($this->headersByName[$headerName]);
    }

    public function getHeadersArrangedByName(): array
    {
        return $this->headersByName;
    }

    public function sendBufferedHeaders(): void
    {
        if (!$this->isBuffering()) {
            return;
        }

        foreach ($this->headersByName as $headerContainer) {
            foreach ($headerContainer->toArray() as $header) {
                $header->send();
            }
        }
    }

    public function clearHeaders(): void
    {
        header_remove();
        $this->headersByName = [];
    }

    public function addCookie(Cookie $cookie): void
    {
        $cookieName = $cookie->getName();

        $this->cookiesByName[$cookieName] = $cookie;

        if (!$this->isBuffering()) {
            $cookie->send();
        }
    }

    public function hasCookie(string $cookieName): bool
    {
        return isset($this->cookiesByName[$cookieName]);
    }

    public function sendBufferedCookies(): void
    {
        if (!$this->isBuffering()) {
            return;
        }

        foreach ($this->cookiesByName as $cookie) {
            $cookie->send();
        }
    }

    public function clearCookies(): void
    {
        $this->cookiesByName = [];
    }

    public function sendContent(string $content): void
    {
        if ($this->compressWithGzip) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }

        echo $content;

        ob_end_flush();
    }

    public function getBufferedContent(): string
    {
        if ($this->isBuffering()) {
            $content = $this->bufferHandler->stop();
        }
        else {
            $content = '';
        }

        return $content;
    }

    public function clear(): void
    {
        if (!$this->isBuffering()) {
            return;
        }

        $this->bufferHandler->clear();
        $this->startBuffer($this->bufferHandler);
    }
}
