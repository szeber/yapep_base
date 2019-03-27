<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

class Regex extends ParamAbstract
{
    const ARRAY_KEY_PATTERN = 'pattern';

    /** @var string */
    protected $pattern;

    public function __construct(string $name, string $pattern)
    {
        parent::__construct($name);

        $this->pattern = $pattern;
    }

    public static function createFromArray(array $paramData)
    {
        static::validateParamData($paramData);

        return new static(
            (string)$paramData['name'],
            (string)$paramData['pattern']
        );
    }

    public static function __set_state(array $state)
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

    protected function getExtraArrayFields(): array
    {
        return [self::ARRAY_KEY_PATTERN => $this->pattern];
    }

    protected static function validateParamData(array $paramData): void
    {
        parent::validateParamData($paramData);

        if (empty($paramData['pattern'])) {
            throw new InvalidArgumentException('No "pattern" value for regex parameter');
        }
    }
}
