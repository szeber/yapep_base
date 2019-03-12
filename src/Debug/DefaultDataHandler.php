<?php
declare(strict_types = 1);

namespace YapepBase\Debug;

use YapepBase\Debug\Item\CurlRequest;
use YapepBase\Debug\Item\Error;
use YapepBase\Debug\Item\Event;
use YapepBase\Debug\Item\General;
use YapepBase\Debug\Item\MemoryUsage;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Debug\Item\Time;

class DefaultDataHandler implements ICanReturnItems
{
    /** @var CurlRequest[] */
    protected $curlRequestItems = [];
    /** @var Error[] */
    protected $errorItems       = [];
    /** @var Event[] */
    protected $eventItems       = [];
    /** @var MemoryUsage[] */
    protected $memoryUsageItems = [];
    /** @var SqlQuery[] */
    protected $sqlQueryItems    = [];
    /** @var Storage[] */
    protected $storageItems     = [];
    /** @var Time[] */
    protected $timeItems        = [];
    /** @var General[] */
    protected $generalItems     = [];

    public function addCurlRequest(CurlRequest $item): void
    {
        $this->curlRequestItems[] = $item;
    }

    public function addError(Error $item): void
    {
        $this->errorItems[] = $item;
    }

    public function addEvent(Event $item): void
    {
        $this->eventItems[] = $item;
    }

    public function addMemoryUsage(MemoryUsage $item): void
    {
        $this->memoryUsageItems[] = $item;
    }

    public function addSqlQuery(SqlQuery $item): void
    {
        $this->sqlQueryItems[] = $item;
    }

    public function addStorage(Storage $item): void
    {
        $this->storageItems[] = $item;
    }

    public function addTime(Time $item): void
    {
        $this->timeItems[] = $item;
    }

    public function addGeneral(General $item): void
    {
        $this->generalItems[] = $item;
    }

    public function getCurlRequestItems(): array
    {
        return $this->curlRequestItems;
    }

    public function getErrorItems(): array
    {
        return $this->errorItems;
    }

    public function getEventItems(): array
    {
        return $this->eventItems;
    }

    public function getMemoryUsageItems(): array
    {
        return $this->memoryUsageItems;
    }

    public function getSqlQueryItems(): array
    {
        return $this->sqlQueryItems;
    }

    public function getStorageItems(): array
    {
        return $this->storageItems;
    }

    public function getTimeItems(): array
    {
        return $this->timeItems;
    }

    public function getGeneralItems(): array
    {
        return $this->generalItems;
    }
}
