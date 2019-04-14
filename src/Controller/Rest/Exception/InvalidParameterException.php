<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class InvalidParameterException extends ExceptionAbstract
{
    const REASON_MISSING       = 'missing';
    const REASON_DUPLICATE     = 'duplicate';
    const REASON_INVALID       = 'invalid';
    const REASON_MUST_BE_EMPTY = 'mustBeEmpty';

    private $invalidParametersByName = [];

    public function __construct()
    {
        parent::__construct('An invalid parameter was sent to the endpoint, or a required parameter is missing');
    }

    public function addInvalidParameter(string $parameterName, string $reason)
    {
        $this->invalidParametersByName[$parameterName] = $reason;
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 400;
    }
}
