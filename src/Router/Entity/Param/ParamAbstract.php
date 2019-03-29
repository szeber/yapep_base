<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

abstract class ParamAbstract implements IParam
{
    const ARRAY_KEY_CLASS = 'paramClass';
    const ARRAY_KEY_NAME  = 'name';

    /** @var string */
    protected $name;

    abstract protected function getExtraArrayFields(): array;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static((string)$paramData[self::ARRAY_KEY_NAME]);
    }

    public function toArray(): array
    {
        $array = [
            self::ARRAY_KEY_CLASS => get_class($this),
            self::ARRAY_KEY_NAME  => $this->name,
        ];

        return array_merge($array, $this->getExtraArrayFields());
    }

    protected static function validateParamData(array $paramData): void
    {
        if (!isset($paramData[self::ARRAY_KEY_NAME]) || strlen($paramData[self::ARRAY_KEY_NAME]) == 0) {
            throw new InvalidArgumentException('No name set for the param');
        }
    }

    public static function __set_state(array $state)
    {
        return new static($state[self::ARRAY_KEY_NAME]);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
