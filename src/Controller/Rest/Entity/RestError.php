<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest\Entity;

class RestError
{
    const KEY_ERROR_CODE        = 'errorCode';
    const KEY_ERROR_DESCRIPTION = 'errorDescription';
    const KEY_PARAMS            = 'params';

    /** @var string */
    protected $errorCode;
    /** @var string */
    protected $description;
    /** @var array|null */
    protected $params;

    public function __construct(string $errorCode, string $description, ?array $params)
    {
        $this->errorCode   = $errorCode;
        $this->description = $description;
        $this->params      = $params;
    }

    public function toArray(): array
    {
        $array = [
            self::KEY_ERROR_CODE        => $this->errorCode,
            self::KEY_ERROR_DESCRIPTION => $this->description,
        ];

        if (!is_null($this->params)) {
            $array[self::KEY_PARAMS] = $this->params;
        }

        return $array;
    }
}
