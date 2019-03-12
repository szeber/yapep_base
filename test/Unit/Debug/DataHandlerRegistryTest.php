<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Debug\DataHandlerRegistry;
use YapepBase\Debug\ICanReturnItems;
use YapepBase\Debug\Item\CurlRequest;
use YapepBase\Debug\Item\Error;
use YapepBase\Debug\Item\Event;
use YapepBase\Debug\Item\General;
use YapepBase\Debug\Item\MemoryUsage;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Debug\Item\Time;
use YapepBase\Exception\Exception;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class DataHandlerRegistryTest extends TestAbstract
{
    /** @var DataHandlerRegistry */
    protected $registry;
    /** @var float */
    protected $initiatedAt = 0.1;
    /** @var MockInterface */
    protected $dataHandler1;
    /** @var MockInterface */
    protected $dataHandler2;
    /** @var string */
    protected $name1 = 'name1';
    /** @var string */
    protected $name2 = 'name2';

    protected function setUp(): void
    {
        parent::setUp();
        $this->expectSetInitiatedAt();

        $this->registry = new DataHandlerRegistry();

        $this->dataHandler1 = Mockery::mock(ICanReturnItems::class);
        $this->dataHandler2 = Mockery::mock(ICanReturnItems::class);
    }

    protected function expectSetInitiatedAt()
    {
        $dateHelper = Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestampUs')
            ->andReturn($this->initiatedAt)
            ->getMock();

        $this->pimpleContainer[DateHelper::class] = $dateHelper;
    }

    public function testRegister_shouldRegisterGivenHandler()
    {
        $this->registry->register($this->name1, $this->dataHandler1);

        $this->assertSame($this->dataHandler1, $this->registry->getHandler($this->name1));
    }

    public function testRegisterWhenNameAlreadyUsed_shouldThrowException()
    {
        $this->registry->register($this->name1, $this->dataHandler1);

        $this->expectException(Exception::class);
        $this->registry->register($this->name1, $this->dataHandler2);
    }

    public function testUnRegister_shouldRemoveGivenHandler()
    {
        $this->registry->register($this->name1, $this->dataHandler1);

        $this->registry->unregister($this->name1);

        $this->assertNull($this->registry->getHandler($this->name1));
    }

    public function testClear_shouldRemoveEveryHandler()
    {
        $this->registerHandlers();

        $this->registry->clear();

        $this->assertNull($this->registry->getHandler($this->name1));
        $this->assertNull($this->registry->getHandler($this->name2));
    }

    public function testAddCurlRequest_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new CurlRequest('HTTP', 'GET', 'url');

        $this->expectAddItemCalledOnHandlers('addCurlRequest', $item);
        $this->registry->addCurlRequest($item);
    }

    public function testAddError_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new Error(1, 'm', 'file', 1, [], '1');

        $this->expectAddItemCalledOnHandlers('addError', $item);
        $this->registry->addError($item);
    }

    public function testAddEvent_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new Event('name', []);

        $this->expectAddItemCalledOnHandlers('addEvent', $item);
        $this->registry->addEvent($item);
    }

    public function testAddMemoryUsage_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new MemoryUsage('name');

        $this->expectAddItemCalledOnHandlers('addMemoryUsage', $item);
        $this->registry->addMemoryUsage($item);
    }

    public function testAddSqlQuery_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new SqlQuery('dsn', 'query', []);

        $this->expectAddItemCalledOnHandlers('addSqlQuery', $item);
        $this->registry->addSqlQuery($item);
    }

    public function testAddStorage_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new Storage(Storage::METHOD_GET, 'key');

        $this->expectAddItemCalledOnHandlers('addStorage', $item);
        $this->registry->addStorage($item);
    }

    public function testAddTime_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new Time('name');

        $this->expectAddItemCalledOnHandlers('addTime', $item);
        $this->registry->addTime($item);
    }

    public function testAddGeneral_shouldAddToAllHandlers()
    {
        $this->registerHandlers();
        $item = new General('name');

        $this->expectAddItemCalledOnHandlers('addGeneral', $item);
        $this->registry->addGeneral($item);
    }

    protected function registerHandlers()
    {
        $this->registry->register($this->name1, $this->dataHandler1);
        $this->registry->register($this->name2, $this->dataHandler2);
    }

    protected function expectAddItemCalledOnHandlers(string $adderMethodName, $expectedItem)
    {
        foreach ([$this->dataHandler1, $this->dataHandler2] as $dataHandler) {
            $dataHandler
                ->shouldReceive($adderMethodName)
                ->once()
                ->with(
                    Mockery::on(function ($item) use ($expectedItem) {
                        return json_encode($item) == json_encode($expectedItem);
                    })
                );
        }
    }
}
