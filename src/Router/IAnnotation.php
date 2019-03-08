<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Exception\InvalidArgumentException;

interface IAnnotation
{
    /**
     * @param array $annotation
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $annotation);

    /**
     * @param $state
     *
     * @return static
     */
    public static function __set_state($state);
}
