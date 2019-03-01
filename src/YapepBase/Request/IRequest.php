<?php
declare(strict_types=1);

namespace YapepBase\Request;

interface IRequest
{
    const METHOD_CLI = 'CLI';
    const METHOD_HTTP_GET = 'GET';
    const METHOD_HTTP_POST = 'POST';
    const METHOD_HTTP_PUT = 'PUT';
    const METHOD_HTTP_HEAD = 'HEAD';
    const METHOD_HTTP_OPTIONS = 'OPTIONS';
    const METHOD_HTTP_DELETE = 'DELETE';

    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_CLI = 'cli';

    /**
     * Returns the target of the request. (eg the URI for HTTP requests)
     */
    public function getTarget(): string;

    /**
     * Returns the method of the request
     */
    public function getMethod(): string;

    /**
     * Returns the specified route param, or the default value if it's not set.
     */
    public function getParam(string $name, $default = null);

    /**
     * Sets a route param
     */
    public function setParam(string $name, $value): void;

    /**
     * Returns the protocol used in the request.
     */
    public function getProtocol(): string;
}
