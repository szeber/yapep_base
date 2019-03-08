<?php
declare(strict_types=1);

namespace YapepBase\View\Escape;

class Html extends EscapeAbstract
{
    protected function escapeBool(bool $value)
    {
        return $value;
    }

    protected function escapeInt(int $value)
    {
        return $value;
    }

    protected function escapeFloat(float $value)
    {
        return $value;
    }

    protected function escapeNull()
    {
        return null;
    }

    protected function escapeString(string $value)
    {
        return htmlspecialchars($value);
    }

    protected function escapeArray(array $value)
    {
        foreach ($value as $elementKey => $elementValue) {
            $value[$elementKey] = $this->__escape($elementValue);
        }
        return $value;
    }
}
