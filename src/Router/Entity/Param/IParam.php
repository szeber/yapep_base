<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;

interface IParam
{
    /**
     * Creates the object from the given array
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function createFromArray(array $paramData);

    /**
     * Returns the array representation of the object
     */
    public function toArray(): array;

    /**
     * @return static
     */
    public static function __set_state(array $state);

    public function getName(): string;

    public function getPattern(): string;
}
