<?php
declare(strict_types=1);

namespace YapepBase\Router\DataObject\Param;

use YapepBase\Exception\InvalidArgumentException;

class Enum extends ParamAbstract
{
    /** @var array */
    protected $values = [];

    public function __construct(array $paramData)
    {
        parent::__construct($paramData);

        if (!isset($paramData['values'])) {
            throw new InvalidArgumentException('No "values" value for enum parameter');
        }

        if (!is_array($paramData['values'])) {
            throw new InvalidArgumentException(
                'The "values" value should be an array, got ' . gettype($paramData['values'])
            );
        }

        $this->values = $paramData['values'];
    }

    public function getPattern(): string
    {
        return implode('|', array_map(function($value) {
            return addcslashes($value, '|');
        }, $this->values));
    }
}
