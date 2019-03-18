<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug;

use YapepBase\Debug\DefaultDataHandler;
use YapepBase\Debug\Item\CurlRequest;
use YapepBase\Debug\Item\Error;
use YapepBase\Debug\Item\Event;
use YapepBase\Debug\Item\General;
use YapepBase\Debug\Item\MemoryUsage;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Debug\Item\Time;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class DefaultDataHandlerTest extends TestAbstract
{
    /** @var DefaultDataHandler */
    protected $dataHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataHandler = new DefaultDataHandler();
    }

    public function testAddCurlRequest_shouldStoreGivenItem()
    {
        $item = new CurlRequest(new DateHelper(), 'HTTP', 'GET', 'url');
        $this->dataHandler->addCurlRequest($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getCurlRequestItems(), $item);
    }

    public function testAddError_shouldStoreGivenItem()
    {
        $item = new Error(1, 'm', 'file', 1, [], '1');
        $this->dataHandler->addError($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getErrorItems(), $item);
    }

    public function testAddEvent_shouldStoreGivenItem()
    {
        $item = new Event(new DateHelper(), 'name', []);
        $this->dataHandler->addEvent($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getEventItems(), $item);
    }

    public function testAddMemoryUsage_shouldStoreGivenItem()
    {
        $item = new MemoryUsage('name');
        $this->dataHandler->addMemoryUsage($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getMemoryUsageItems(), $item);
    }

    public function testAddSqlQuery_shouldStoreGivenItem()
    {
        $item = new SqlQuery(new DateHelper(), 'dsn', 'query', []);
        $this->dataHandler->addSqlQuery($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getSqlQueryItems(), $item);
    }

    public function testAddStorage_shouldStoreGivenItem()
    {
        $item = new Storage(new DateHelper(), Storage::METHOD_GET, 'key');
        $this->dataHandler->addStorage($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getStorageItems(), $item);
    }

    public function testAddTime_shouldStoreGivenItem()
    {
        $item = new Time(new DateHelper(), 'name');
        $this->dataHandler->addTime($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getTimeItems(), $item);
    }

    public function testAddGeneral_shouldStoreGivenItem()
    {
        $item = new General(new DateHelper(), 'name');
        $this->dataHandler->addGeneral($item);

        $this->assertGivenItemStoredOnly($this->dataHandler->getGeneralItems(), $item);
    }

    protected function assertGivenItemStoredOnly(array $items, $item)
    {
        $this->assertCount(1, $items);
        $this->assertSame($item, $items[0]);
    }
}
