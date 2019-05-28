<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

interface IException
{
    const KEY_ERROR_CODE        = 'errorCode';
    const KEY_ERROR_DESCRIPTION = 'errorDescription';
    const KEY_PARAMS            = 'params';

    public function getRecommendedHttpStatusCode(): int;

    public function getCodeString(): string;

    public function getRequestParams(): ?array;

    public function toArray(): array;
}
