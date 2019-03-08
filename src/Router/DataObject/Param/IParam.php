<?php
declare(strict_types=1);

namespace YapepBase\Router\DataObject\Param;

interface IParam
{

    public const TYPE_NUMERIC                = 'num';
    public const TYPE_ALPHA                  = 'alpha';
    public const TYPE_ALPHA_NUMERIC          = 'alnum';
    public const TYPE_ALPHA_NUMERIC_EXTENDED = 'alnumext';
    public const TYPE_UUID                   = 'uuid';
    public const TYPE_REGEX                  = 'regex';
    public const TYPE_ENUM                   = 'enum';

    public const BUILT_IN_TYPE_MAP = [
        self::TYPE_NUMERIC                => Numeric::class,
        self::TYPE_ALPHA                  => Alpha::class,
        self::TYPE_ALPHA_NUMERIC          => AlphaNumeric::class,
        self::TYPE_ALPHA_NUMERIC_EXTENDED => AlphaNumericExtended::class,
        self::TYPE_UUID                   => Uuid::class,
        self::TYPE_REGEX                  => Regex::class,
        self::TYPE_ENUM                   => Enum::class,
    ];

    /**
     * @param array $paramData
     *
     * @return static
     */
    public static function createFromArray(array $paramData);

    /**
     * @param array $state
     *
     * @return static
     */
    public static function __set_state($state);

    public function getName(): string;

    public function getPattern(): string;
}
