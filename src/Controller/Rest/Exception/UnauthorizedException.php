<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class UnauthorizedException extends Exception
{
    public function __construct()
    {
        parent::__construct('The authenticated account is not authorized to perform the requested action');
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 403;
    }

    public function getCodeString(): string
    {
        return 'UnauthorizedError';
    }
}
