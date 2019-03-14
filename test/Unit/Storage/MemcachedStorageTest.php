<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\StorageException;
use YapepBase\Helper\DateHelper;
use YapepBase\Storage\KeyGenerator;
use YapepBase\Storage\MemcachedStorage;

class MemcachedStorageTest extends TestAbstract
{
    /** @var MockInterface */
    protected $memcached;

    protected $key    = 'key1';
    protected $data   = ['test' => 1];
    protected $ttl    = 10;
    protected $offset = 2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memcached = \Mockery::mock(\Memcached::class);
        $this->initDateHelper(new DateHelper());
        $this->initDebugDataHandler();
    }

    public function testSetWhenReadOnly_shouldThrowException()
    {
        $this->expectException(StorageException::class);

        $this->getStorage(true)->set($this->key, $this->data);
    }

    public function testSetWhenSucceeds_shouldStoreValueAndCreateDebug()
    {
        $this->expectSetToMemcached($this->key, true);
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $this->data);

        $this->getStorage()->set($this->key, $this->data, $this->ttl);
    }

    public function testSetStoreFails_shouldThrowException()
    {
        $memcachedResultCode    = \Memcached::RES_AUTH_FAILURE;
        $memcachedResultMessage = 'Message';

        $this->expectSetToMemcached($this->key, false);
        $this->expectGetResultCodeFromMemcached($memcachedResultCode);
        $this->expectGetResultMessageFromMemcached($memcachedResultMessage);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage($memcachedResultMessage);
        $this->expectExceptionCode($memcachedResultCode);

        $this->getStorage()->set($this->key, $this->data, $this->ttl);
    }

    public function testSetStoreFailsWithNotStore_shouldReturn()
    {
        $memcachedResultCode = \Memcached::RES_NOTSTORED;

        $this->expectSetToMemcached($this->key, false);
        $this->expectGetResultCodeFromMemcached($memcachedResultCode);
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $this->data);

        $this->getStorage()->set($this->key, $this->data, $this->ttl);
    }

    public function testGetWhenKeyFound_shouldReturnRetrievedData()
    {
        $this->expectGetFromMemcached($this->key, $this->data);
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, $this->data);

        $result = $this->getStorage()->get($this->key);

        $this->assertSame($this->data, $result);
    }

    public function acceptableGetResultCodeProvider(): array
    {
        return [
            [\Memcached::RES_NOTFOUND],
            [\Memcached::RES_SUCCESS],
        ];
    }

    /**
     * @dataProvider acceptableGetResultCodeProvider
     */
    public function testGetWhenResultFalse_shouldReturn(int $resultCode)
    {
        $this->expectGetFromMemcached($this->key, false);
        $this->expectGetResultCodeFromMemcached($resultCode);
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, false);

        $result = $this->getStorage()->get($this->key);

        $this->assertFalse($result);
    }

    public function testGetWhenFails_shouldThrowException()
    {
        $memcachedResultCode    = \Memcached::RES_AUTH_FAILURE;
        $memcachedResultMessage = 'Message';

        $this->expectGetFromMemcached($this->key, false);
        $this->expectGetResultCodeFromMemcached($memcachedResultCode);
        $this->expectGetResultMessageFromMemcached($memcachedResultMessage);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage($memcachedResultMessage);
        $this->expectExceptionCode($memcachedResultCode);

        $this->getStorage()->get($this->key);
    }

    public function testDeleteWhenReadOnly_shouldThrowException()
    {
        $this->expectException(StorageException::class);
        $this->getStorage(true)->delete($this->key);
    }

    public function testDelete_shouldDeleteItemAndSetDebug()
    {
        $this->expectDeleteFromMemcached($this->key);
        $this->expectAddStorageDebug(Storage::METHOD_DELETE, $this->key, null);

        $this->getStorage()->delete($this->key);
    }

    public function testClearWhenReadOnly_shouldThrowException()
    {
        $this->expectException(StorageException::class);
        $this->getStorage(true)->clear();
    }

    public function testClear_shouldClearMemcached()
    {
        $this->expectClearMemcached();
        $this->expectAddStorageDebug(Storage::METHOD_CLEAR, null, null);

        $this->getStorage()->clear();
    }

    public function testIncrementWhenReadOnly_shouldThrowException()
    {
        $this->expectException(StorageException::class);
        $this->getStorage(true)->increment($this->key, $this->offset, $this->ttl);
    }

    public function testIncrementWhenFails_shouldThrowException()
    {
        $this->expectIncrementInMemcached(false);
        $this->expectException(StorageException::class);

        $this->getStorage()->increment($this->key, $this->offset, $this->ttl);
    }

    public function testIncrementWhenSucceeds_shouldReturnResult()
    {
        $expectedResult = 3;

        $this->expectIncrementInMemcached($expectedResult);
        $this->expectAddStorageDebug(Storage::METHOD_INCREMENT, $this->key, $this->offset);

        $result = $this->getStorage()->increment($this->key, $this->offset, $this->ttl);

        $this->assertSame($expectedResult, $result);
    }

    protected function expectSetToMemcached(string $key, bool $expectedResult)
    {
        $this->memcached
            ->shouldReceive('set')
            ->once()
            ->with($key, $this->data, $this->ttl)
            ->andReturn($expectedResult);
    }

    protected function expectGetFromMemcached(string $key, $expectedResult)
    {
        $this->memcached
            ->shouldReceive('get')
            ->once()
            ->with($key)
            ->andReturn($expectedResult);
    }

    protected function expectDeleteFromMemcached(string $key)
    {
        $this->memcached
            ->shouldReceive('delete')
            ->once()
            ->with($key);
    }

    protected function expectClearMemcached()
    {
        $this->memcached
            ->shouldReceive('flush')
            ->once();
    }

    protected function expectIncrementInMemcached($expectedResult)
    {
        $this->memcached
            ->shouldReceive('increment')
            ->once()
            ->with($this->key, $this->offset, 0, $this->ttl)
            ->andReturn($expectedResult);
    }

    protected function expectGetResultCodeFromMemcached(int $expectedResult)
    {
        $this->memcached
            ->shouldReceive('getResultCode')
            ->once()
            ->andReturn($expectedResult);
    }

    protected function expectGetResultMessageFromMemcached(string $expectedResult)
    {
        $this->memcached
            ->shouldReceive('getResultMessage')
            ->once()
            ->andReturn($expectedResult);
    }

    protected function getStorage(bool $readOnly = false): MemcachedStorage
    {
        return new MemcachedStorage($this->memcached, new KeyGenerator(false), $readOnly);
    }
}
