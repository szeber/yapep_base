<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

abstract class ParamAbstract implements IParam
{
    /** @var string */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static((string)$paramData['name']);
    }

    public function toArray(): array
    {
        return ['name' => $this->name];
    }

    protected static function validateParamData(array $paramData): void
    {
        if (!isset($paramData['name']) || strlen($paramData['name']) == 0) {
            throw new InvalidArgumentException('No name set for the param');
        }
    }

    public static function __set_state(array $state)
    {
        return new static($state['name']);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
