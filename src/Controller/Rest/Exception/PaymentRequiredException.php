<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class PaymentRequiredException extends Exception
{
    public function __construct()
    {
        parent::__construct('The authenticated account is in debt');
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 402;
    }

    public function getCodeString(): string
    {
        return 'PaymentRequired';
    }
}
