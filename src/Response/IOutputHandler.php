<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Exception\Exception;

/**
 * Classes implementing this interface handle the raw output to the browser,
 * etc. It has been implemented to separate the PHP-dependant code parts.
 */
interface IOutputHandler extends IHeaderHandler, ICookieHandler
{
    /**
     * Tells if the output handler is currently buffering
     */
    public function isBuffering(): bool;

    /**
     * Stars buffering
     *
     * @throws Exception
     */
    public function startBuffer(OutputBufferHandler $bufferHandler): void;

    /**
     * Stops the buffering and echos the already buffered content
     */
    public function stopBuffer(): void;

    /**
     * Sends out the given content/body
     *
     * @param string $content
     */
    public function sendContent(string $content): void;

    /**
     * Returns the buffered content.
     */
    public function getBufferedContent(): string;
}
