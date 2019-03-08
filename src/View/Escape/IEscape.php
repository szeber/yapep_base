<?php
declare(strict_types=1);

namespace YapepBase\View\Escape;

interface IEscape
{
    /**
     * Escapes the given value if possible and returns it
     */
    public function __escape($value);
}
