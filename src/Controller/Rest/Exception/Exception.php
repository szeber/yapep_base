<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class Exception extends \YapepBase\Controller\Exception\Exception
{
    const DEFAULT_MESSAGE = 'An internal error occurred while serving the request. The error has been logged, please try your request again later';

    /** @var array */
    protected $requestParams = [];

    public function __construct(string $message = self::DEFAULT_MESSAGE, array $requestParams = [])
    {
        $this->requestParams = $requestParams;
        parent::__construct($message);
    }

    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 500;
    }

    public function getCodeString(): string
    {
        return 'InternalError';
    }
}
