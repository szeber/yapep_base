<?php
declare(strict_types=1);

namespace YapepBase\Helper;

class DateHelper extends HelperAbstract
{
    public function getCurrentTimestamp(): int
    {
        return time();
    }

    public function getCurrentTimestampUs(): float
    {
        return microtime(true);
    }
}
