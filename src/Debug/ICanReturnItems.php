<?php
declare(strict_types=1);

namespace YapepBase\Debug;

use YapepBase\Debug\Item\CurlRequest;
use YapepBase\Debug\Item\Error;
use YapepBase\Debug\Item\Event;
use YapepBase\Debug\Item\General;
use YapepBase\Debug\Item\MemoryUsage;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Debug\Item\Time;

interface ICanReturnItems extends ICanStoreItems
{
    /**
     * @return CurlRequest[]
     */
    public function getCurlRequestItems(): array;

    /**
     * @return Error[]
     */
    public function getErrorItems(): array;

    /**
     * @return Event[]
     */
    public function getEventItems(): array;

    /**
     * @return MemoryUsage[]
     */
    public function getMemoryUsageItems(): array;

    /**
     * @return SqlQuery[]
     */
    public function getSqlQueryItems(): array;

    /**
     * @return Storage[]
     */
    public function getStorageItems(): array;

    /**
     * @return Time[]
     */
    public function getTimeItems(): array;

    /**
     * @return General[]
     */
    public function getGeneralItems(): array;
}
