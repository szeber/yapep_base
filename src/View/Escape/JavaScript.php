<?php
declare(strict_types = 1);

namespace YapepBase\View\Escape;

class JavaScript extends EscapeAbstract
{
    protected function escapeBool(bool $value)
    {
        return json_encode($value);
    }

    protected function escapeInt(int $value)
    {
        return json_encode($value);
    }

    protected function escapeFloat(float $value)
    {
        return json_encode($value);
    }

    protected function escapeNull()
    {
        return json_encode(null);
    }

    protected function escapeString(string $value)
    {
        return json_encode($value);
    }

    protected function escapeArray(array $value)
    {
        return json_encode($value);
    }
}
