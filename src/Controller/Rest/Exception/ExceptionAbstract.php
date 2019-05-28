<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Exception;

use Lukasoppermann\Httpstatus\Httpstatus;
use YapepBase\Controller\Exception\Exception;

abstract class ExceptionAbstract extends Exception implements IException
{
    /** @var array|null */
    protected $requestParams;

    final public function getCodeString(): string
    {
        return (new Httpstatus())->getReasonPhrase($this->getRecommendedHttpStatusCode());
    }

    public function setRequestParams(array $requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    public function getRequestParams(): ?array
    {
        return $this->requestParams;
    }

    public function toArray(): array
    {
        $array = [
            self::KEY_ERROR_CODE        => $this->getCodeString(),
            self::KEY_ERROR_DESCRIPTION => $this->getMessage(),
        ];

        if (!is_null($this->requestParams)) {
            $array[self::KEY_PARAMS] = $this->requestParams;
        }

        return $array;
    }
}
