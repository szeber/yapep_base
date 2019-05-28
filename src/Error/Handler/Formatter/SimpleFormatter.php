<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler\Formatter;

class SimpleFormatter implements IFormatter
{
    public function format(string $sectionName, $data): string
    {
        if (empty($data)) {
            return '';
        }

        return '----- ' . $sectionName . ' -----' . PHP_EOL . PHP_EOL
            . print_r($data, true) . PHP_EOL . PHP_EOL;
    }
}
