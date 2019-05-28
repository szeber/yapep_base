<?php
declare(strict_types=1);

namespace YapepBase\Error\Registry;

use YapepBase\Error\Entity\Error;

interface IIdGenerator
{
    /**
     * Generates the id into the given Error object
     */
    public function generateId(Error $error): void;
}
