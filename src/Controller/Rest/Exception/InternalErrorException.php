<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class InternalErrorException extends ExceptionAbstract
{
    public function getRecommendedHttpStatusCode(): int
    {
        return 500;
    }
}
