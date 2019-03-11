<?php
declare(strict_types=1);

namespace YapepBase\Helper;

class DateHelper extends HelperAbstract
{
    public function getCurrentTimestamp(): int
    {
        return time();
    }

    public function getCurrentTimestampMs(): float
    {
        return microtime(true);
    }
}
