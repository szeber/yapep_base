<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

/**
 * Maps the param types to their representation class
 */
class Mapper
{
    public const TYPE_NUMERIC                = 'num';
    public const TYPE_ALPHA                  = 'alpha';
    public const TYPE_ALPHA_NUMERIC          = 'alnum';
    public const TYPE_ALPHA_NUMERIC_EXTENDED = 'alnumext';
    public const TYPE_UUID                   = 'uuid';
    public const TYPE_REGEX                  = 'regex';
    public const TYPE_ENUM                   = 'enum';

    protected const BUILT_IN_TYPE_MAP = [
        self::TYPE_NUMERIC                => Numeric::class,
        self::TYPE_ALPHA                  => Alpha::class,
        self::TYPE_ALPHA_NUMERIC          => AlphaNumeric::class,
        self::TYPE_ALPHA_NUMERIC_EXTENDED => AlphaNumericExtended::class,
        self::TYPE_UUID                   => Uuid::class,
        self::TYPE_REGEX                  => Regex::class,
        self::TYPE_ENUM                   => Enum::class,
    ];

    /**
     * @throws InvalidArgumentException
     */
    public static function getClassByType(string $type): string
    {
        if (!isset(self::BUILT_IN_TYPE_MAP[$type])) {
            throw new InvalidArgumentException('Type ' . $type . ' is not mapped to a Class');
        }

        return self::BUILT_IN_TYPE_MAP[$type];
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getTypeByClass(string $class): string
    {
        $result = array_search($class, self::BUILT_IN_TYPE_MAP);

        if ($result === false) {
            throw new InvalidArgumentException('Class ' . $class . ' is not mapped to a type');
        }

        return $result;
    }
}
