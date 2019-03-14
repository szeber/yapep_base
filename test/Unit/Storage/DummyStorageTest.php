<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\Debug\Item\Storage;
use YapepBase\Helper\DateHelper;
use YapepBase\Storage\IKeyGenerator;

class DummyStorageTest extends TestAbstract
{
    /** @var MockInterface */
    protected $keyGenerator;
    /** @var string */
    protected $key = 'key1';
    /** @var string */
    protected $data = 'value1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyGenerator = \Mockery::mock(IKeyGenerator::class);
        $this->initDateHelper(new DateHelper());
        $this->initDebugDataHandler();
    }

    public function testSetWhenNotStoringValues_shouldOnlyCreateDebugEntry()
    {
        $dummyStorage = $this->getDummyStorage(false);

        $this->expectGenerateKey();
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $this->data);
        $dummyStorage->set($this->key, $this->data);

        $this->assertFalse($dummyStorage->has($this->key));
    }

    public function testSetWhenStoringValues_shouldStore()
    {
        $dummyStorage = $this->getDummyStorage(true);

        $this->expectGenerateKey();
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $this->data);
        $dummyStorage->set($this->key, $this->data);

        $this->assertEquals($this->data, $dummyStorage->getSimple($this->key));
    }

    public function testGetWhenDataFound_shouldReturnSetData()
    {
        $dummyStorage = $this->getDummyStorage(true);
        $dummyStorage->setSimple($this->key, $this->data);

        $this->expectGenerateKey();
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, $this->data);
        $result = $dummyStorage->get($this->key);

        $this->assertEquals($this->data, $result);
    }

    public function testGetWhenDataNotFound_shouldReturnNull()
    {
        $dummyStorage = $this->getDummyStorage(true);

        $this->expectGenerateKey();
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, null);
        $result = $dummyStorage->get($this->key);

        $this->assertNull($result);
    }

    public function testDelete_shouldDeleteGivenKey()
    {
        $dummyStorage = $this->getDummyStorage(true);
        $dummyStorage->setSimple($this->key, $this->data);

        $this->expectGenerateKey();
        $this->expectAddStorageDebug(Storage::METHOD_DELETE, $this->key);
        $dummyStorage->delete($this->key);

        $this->assertFalse($dummyStorage->has($this->key));
    }

    public function testClear_shouldDeleteEverything()
    {
        $key2         = 'key2';
        $dummyStorage = $this->getDummyStorage(true);
        $dummyStorage->setSimple($this->key, $this->data);
        $dummyStorage->setSimple($key2, 'whatever');

        $this->expectAddStorageDebug(Storage::METHOD_CLEAR, null);
        $dummyStorage->clear();

        $this->assertFalse($dummyStorage->has($this->key));
        $this->assertFalse($dummyStorage->has($key2));
    }

    protected function getDummyStorage(bool $storeValues): DummyStorageStub
    {
        return $this->storage = new DummyStorageStub($this->keyGenerator, $storeValues);
    }

    protected function expectGenerateKey()
    {
        $this->keyGenerator
            ->shouldReceive('generate')
            ->once()
            ->with($this->key)
            ->andReturn($this->key);
    }
}
