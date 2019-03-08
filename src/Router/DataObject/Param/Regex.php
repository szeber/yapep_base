<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

use YapepBase\Exception\InvalidArgumentException;

class Regex extends ParamAbstract
{
    /** @var string */
    protected $pattern;

    public function __construct(string $name, string $pattern)
    {
        parent::__construct($name);

        $this->pattern = $pattern;
    }

    protected static function validateParamData(array $paramData): void
    {
        parent::validateParamData($paramData);

        if (!isset($paramData['pattern'])) {
            throw new InvalidArgumentException('No "pattern" value for regex parameter');
        }
    }

    /**
     * @param array $paramData
     *
     * @return static
     */
    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static(
            (string) $paramData['name'],
            (string) $paramData['pattern']
        );
    }

    /**
     * @param array $state
     *
     * @return static
     */
    public static function __set_state($state)
    {
        return new static(
            $state['name'],
            $state['pattern']
        );
    }
    public function getPattern(): string
    {
        return $this->pattern;
    }
}
