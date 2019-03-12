<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

class Enum extends ParamAbstract
{
    /** @var array */
    protected $values = [];

    public function __construct(string $name, array $values)
    {
        parent::__construct($name);

        $this->values = $values;
    }

    protected static function validateParamData(array $paramData): void
    {
        parent::validateParamData($paramData);

        if (!isset($paramData['values'])) {
            throw new InvalidArgumentException('No "values" value for enum parameter');
        }

        if (!is_array($paramData['values'])) {
            throw new InvalidArgumentException(
                'The "values" value should be an array, got ' . gettype($paramData['values'])
            );
        }
    }

    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static(
            (string)$paramData['name'],
            $paramData['values']
        );
    }

    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'values' => $this->values,
        ];
    }

    public static function __set_state(array $state): self
    {
        return new static(
            $state['name'],
            $state['values']
        );
    }

    public function getName(): string
    {
        return parent::getName();
    }

    public function getPattern(): string
    {
        return implode(
            '|',
            array_map(
                function ($value) {
                    return addcslashes($value, '|');
                },
                $this->values
            )
        );
    }
}
