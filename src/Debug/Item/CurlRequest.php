<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

/**
 * Item which represents a Curl Request.
 */
class CurlRequest extends ItemAbstract
{
    use THasExecutionTime;

    /** @var string */
    protected $protocol;
    /** @var string */
    protected $requestMethod;
    /** @var string */
    protected $url;
    /** @var array */
    protected $parameters = [];
    /** @var array */
    protected $headers = [];
    /** @var array */
    protected $options = [];

    public function __construct(
        string $protocol,
        string $requestMethod,
        string $url,
        array $parameters = [],
        array $headers = [],
        array $options = []
    ) {
        $this->protocol      = $protocol;
        $this->requestMethod = $requestMethod;
        $this->url           = $url;
        $this->parameters    = $parameters;
        $this->headers       = $headers;
        $this->options       = $options;

        $this->setStartTime();
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function jsonSerialize()
    {
        return array_merge(
            [
                'protocol'      => $this->protocol,
                'requestMethod' => $this->requestMethod,
                'url'           => $this->url,
                'parameters'    => $this->parameters,
                'headers'       => $this->headers,
                'options'       => $this->options,
            ],
            $this->getDateForJsonSerialize()
        );
    }
}
