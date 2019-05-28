<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler\Formatter;

interface IFormatter
{
    public function format(string $sectionName, $data): string;
}
