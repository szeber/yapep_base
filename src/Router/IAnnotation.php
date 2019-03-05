<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Exception\InvalidArgumentException;

interface IAnnotation
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $annotation);
}
