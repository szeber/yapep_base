<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

/**
 * Interface for custom parameters what can be set from outside.
 */
interface ICustomParams extends IParams
{
    public function set(string $name, $value): void;
}
