<?php
declare(strict_types=1);

namespace YapepBase\Helper;

/**
 * Text related helper functions.
 */
class TextHelper
{
    public function stripWhitespaceDuplicates(string $string): string
    {
        return trim(preg_replace(['#\s{2,}#', '#[\t\n]#'], ' ', $string));
    }
}
