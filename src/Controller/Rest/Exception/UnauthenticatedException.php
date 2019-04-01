<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class UnauthenticatedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This endpoint requires authentication but no "Authorization" header is sent, or the token is invalid');
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 401;
    }

    public function getCodeString(): string
    {
        return 'UnauthenticatedError';
    }
}
