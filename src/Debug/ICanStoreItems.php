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

interface ICanStoreItems
{
    public function addCurlRequest(CurlRequest $item): void;

    public function addError(Error $item): void;

    public function addEvent(Event $item): void;

    public function addMemoryUsage(MemoryUsage $item): void;

    public function addSqlQuery(SqlQuery $item): void;

    public function addStorage(Storage $item): void;

    public function addTime(Time $item): void;

    public function addGeneral(General $item): void;
}
