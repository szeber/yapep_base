<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class ResourceDoesNotExistException extends Exception
{
    public function __construct(string $method, string $resource)
    {
        $message = 'The requested endpoint exists, but does not support the specified method: ' . $method . ' ' . $resource;
        parent::__construct($message);
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 405;
    }

    public function getCodeString(): string
    {
        return 'ResourceDoesNotExist';
    }
}
