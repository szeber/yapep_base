<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class NotFoundException extends ExceptionAbstract
{
    public function __construct(string $method, string $resource)
    {
        $message = 'The requested endpoint exists, but does not support the specified method: ' . $method . ' ' . $resource;
        parent::__construct($message);
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 404;
    }
}
