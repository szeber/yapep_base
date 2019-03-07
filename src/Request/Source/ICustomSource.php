<?php
declare(strict_types=1);

namespace YapepBase\Request\Source;

/**
 * Interface for custom parameters what can be set from outside.
 */
interface ICustomSource extends ISource
{
    public function set(string $name, $value): void;
}
