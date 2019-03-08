<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

use YapepBase\Exception\InvalidArgumentException;

abstract class ParamAbstract implements IParam
{
    /** @var string */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param array $paramData
     *
     * @return static
     */
    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static((string) $paramData['name']);
    }

    protected static function validateParamData(array $paramData): void
    {
        if (!isset($paramData['name']) || strlen($paramData['name']) == 0) {
            throw new InvalidArgumentException('No name set for the param');
        }
    }

    /**
     * @param array $state
     *
     * @return static
     */
    public static function __set_state($state)
    {
        return new static(
            $state['name']
        );
    }

    public function getName(): string
    {
        return $this->name;
    }
}
