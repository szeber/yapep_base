<?php
declare(strict_types=1);

namespace YapepBase\Request;

use Emul\Server\ServerData;
use YapepBase\Application;
use YapepBase\Helper\ArrayHelper;
use YapepBase\Request\Source\ISource;
use YapepBase\Request\Source\IFiles;
use YapepBase\Request\Source\CustomParams;

/**
 * Stores the details for the current request.
 */
class HttpRequest implements IRequest
{
    const METHOD_HTTP_GET = 'GET';
    const METHOD_HTTP_POST = 'POST';
    const METHOD_HTTP_PUT = 'PUT';
    const METHOD_HTTP_HEAD = 'HEAD';
    const METHOD_HTTP_OPTIONS = 'OPTIONS';
    const METHOD_HTTP_DELETE = 'DELETE';

    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_CLI = 'cli';

    /** @var ISource */
    protected $queryParams;
    /** @var ISource */
    protected $postParams;
    /** @var ISource */
    protected $cookies;
    /** @var ISource */
    protected $envParams;
    /** @var ISource */
    protected $inputParams;
    /** @var IFiles */
    protected $files;
    /** @var ServerData */
    protected $server;
    /** @var CustomParams */
    protected $customParams;
    /** @var array */
    protected $acceptedContentTypes = [];
    /** @var string */
    protected $targetUri   = '';

    public function __construct(
        ISource $queryParams,
        ISource $postParams,
        ISource $cookies,
        ISource $envParams,
        ISource $inputParams,
        IFiles $files,
        ServerData $server
    ) {
        $this->queryParams  = $queryParams;
        $this->postParams   = $postParams;
        $this->cookies      = $cookies;
        $this->envParams    = $envParams;
        $this->inputParams  = $inputParams;
        $this->files        = $files;
        $this->server       = $server;
        $this->customParams = new CustomParams([]);

        list($this->targetUri) = explode('?', $this->server->getRequestUri(), 2);
    }

    public function getQueryParams(): ISource
    {
        return $this->queryParams;
    }

    public function getPostParams(): ISource
    {
        return $this->postParams;
    }

    public function getCookies(): ISource
    {
        return $this->cookies;
    }

    public function getEnvParams(): ISource
    {
        return $this->envParams;
    }

    public function getInputParams(): ISource
    {
        return $this->inputParams;
    }

    public function getFiles(): IFiles
    {
        return $this->files;
    }

    public function getServer(): ServerData
    {
        return $this->server;
    }

    public function getCustomParams(): CustomParams
    {
        return $this->customParams;
    }

    public function getRequestParamAsInt(string $name, ?int $default = null): ?int
    {
        $queryParam  = $this->queryParams->getAsInt($name);
        $postParam   = $this->postParams->getAsInt($name);
        $customParam = $this->customParams->getAsInt($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsString(string $name, ?string $default = null): ?string
    {
        $queryParam  = $this->queryParams->getAsString($name);
        $postParam   = $this->postParams->getAsString($name);
        $customParam = $this->customParams->getAsString($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsFloat(string $name, ?float $default = null): ?float
    {
        $queryParam  = $this->queryParams->getAsFloat($name);
        $postParam   = $this->postParams->getAsFloat($name);
        $customParam = $this->customParams->getAsFloat($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsArray(string $name, ?array $default = []): ?array
    {
        $queryParam  = $this->queryParams->getAsArray($name);
        $postParam   = $this->postParams->getAsArray($name);
        $customParam = $this->customParams->getAsArray($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsBool(string $name, ?bool $default = null): ?bool
    {
        $queryParam  = $this->queryParams->getAsBool($name);
        $postParam   = $this->postParams->getAsBool($name);
        $customParam = $this->customParams->getAsBool($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getTarget(): string
    {
        return $this->targetUri;
    }

    public function getMethod(): string
    {
        return (string)$this->server->getRequestMethod();
    }

    public function getProtocol(): string
    {
        return $this->server->isHttps()
            ? self::PROTOCOL_HTTPS
            : self::PROTOCOL_HTTP;
    }

    protected function getArrayHelper(): ArrayHelper
    {
        return Application::getInstance()->getDiContainer()->get(ArrayHelper::class);
    }
}
