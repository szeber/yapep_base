<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Entity\Header;
use YapepBase\Response\Entity\HeaderContainer;

interface IHeaderHandler
{
    /**
     * Adds the given header in buffering mode, sends them otherwise.
     */
    public function addHeader(Header $header): void;

    /**
     * Removes the given header
     */
    public function removeHeader(Header $header): void;

    /**
     * Clears all headers with the given name
     */
    public function clearHeadersByName(string $headerName): void;

    /**
     * Removes all previous values of a header and sets the given header.
     */
    public function setHeader(Header $header): void;

    /**
     * Returns all headers with the given name
     *
     * @return Header[]
     */
    public function getHeaders(string $headerName): array;

    /**
     * Tells whether a header with the given name exists
     */
    public function hasHeader(string $headerName): bool;

    /**
     * Returns all headers. The header containers are arranged under the header names.
     *
     * @return HeaderContainer[]
     */
    public function getHeadersArrangedByName(): array;

    /**
     * Sends out the stored/buffered headers.
     *
     * Does not do much when not buffering as the headers will be already sent.
     */
    public function sendBufferedHeaders(): void;

    /**
     * Removes all the headers
     */
    public function clearHeaders(): void;
}
