<?php
declare(strict_types=1);

namespace YapepBase\Entity;

/**
 * Class which represents a value what has never been set.
 */
class NotSetValue implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return null;
    }

    public function __toString(): string
    {
        return '';
    }
}
