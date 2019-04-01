<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

class InvalidPayloadException extends Exception
{
    public function __construct(string $jsonDecodeError)
    {
        $message = 'Failed to decode the request as valid JSON. JSON decode error: ' . $jsonDecodeError;
        parent::__construct($message);
    }

    public function getRecommendedHttpStatusCode(): int
    {
        return 400;
    }

    public function getCodeString(): string
    {
        return 'InvalidPayloadError';
    }
}
