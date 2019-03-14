<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\Exception\File\Exception;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use YapepBase\File\IFileHandler;
use YapepBase\Storage\FileStorage;
use YapepBase\Storage\KeyGenerator;

class FileStorageTest extends TestAbstract
{
    /** @var MockInterface */
    protected $fileHandler;
    /** @var KeyGenerator */
    protected $keyGenerator;
    /** @var string */
    protected $path = '/tmp/path/';
    /** @var string */
    protected $pathWithoutTrailingSlash = '/tmp/path';
    /** @var string */
    protected $key = 'key1';
    /** @var array */
    protected $data = ['test'];
    /** @var int */
    protected $ttl = 2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler  = \Mockery::mock(IFileHandler::class);
        $this->keyGenerator = new KeyGenerator(false);
    }

    public function testConstructWhenPathEmpty_shouldThrowException()
    {
        $this->expectException(ParameterException::class);

        $this->getStorageForConstructTest('');
    }

    public function testConstructWhenPathNotExistAndCantCreate_shouldThrowException()
    {
        $this->expectCheckPathExists(false);
        $this->expectMakeDirectoryFails();
        $this->expectException(StorageException::class);

        $this->getStorageForConstructTest($this->path);
    }

    public function testConstructWhenPathIsNotDirectory_shouldThrowException()
    {
        $this->expectCheckPathExists(true);
        $this->expectCheckIsDirectory(false);
        $this->expectException(StorageException::class);

        $this->getStorageForConstructTest($this->pathWithoutTrailingSlash);
    }

    public function testConstructWhenPathIsNotWritable_shouldThrowException()
    {
        $this->expectCheckPathExists(true);
        $this->expectCheckIsDirectory(true);
        $this->expectCheckIsWritable(false);
        $this->expectException(StorageException::class);

        $this->getStorageForConstructTest($this->pathWithoutTrailingSlash);
    }

    public function testConstructWhenPathDoesNotHaveTrailingSlash_shouldAddIt()
    {
        $storage = $this->getStorage($this->pathWithoutTrailingSlash);

        $this->assertEquals($this->path, $storage->getPath());
    }

    public function testSetWhenReadOnly_shouldThrowException()
    {
        $this->expectCheckPathExists(true);
        $this->expectCheckIsDirectory(true);
        $storage = $this->getStorageForConstructTest($this->path, true);

        $this->expectException(StorageException::class);
        $storage->set($this->key, $this->data);
    }

    public function testSetWhenStoreFails_shouldThrowException()
    {
        $storage = $this->getStorage($this->path);

        $this->expectWriteFails();

        $this->expectException(StorageException::class);
        $storage->set($this->key, $this->data, $this->ttl);
    }

    protected function getStorageForConstructTest(string $path, bool $readOnly = false): FileStorage
    {
        return new FileStorage($this->fileHandler, $this->keyGenerator, $path, $readOnly);
    }

    protected function getStorage(string $path, bool $readOnly = false): FileStorage
    {
        $this->expectCheckPathExists(true);
        $this->expectCheckIsDirectory(true);
        $this->expectCheckIsWritable(true);

        return new FileStorage($this->fileHandler, $this->keyGenerator, $path, $readOnly);
    }

    protected function expectCheckPathExists(bool $expectedResult)
    {
        $this->fileHandler
            ->shouldReceive('checkIsPathExists')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);
    }

    protected function expectCheckIsDirectory(bool $expectedResult)
    {
        $this->fileHandler
            ->shouldReceive('checkIsDirectory')
            ->once()
            ->with($this->pathWithoutTrailingSlash)
            ->andReturn($expectedResult);
    }

    protected function expectCheckIsWritable(bool $expectedResult)
    {
        $this->fileHandler
            ->shouldReceive('checkIsWritable')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);
    }

    protected function expectMakeDirectoryFails()
    {
        $this->fileHandler
            ->shouldReceive('makeDirectory')
            ->once()
            ->with($this->path, 0644 | 0111, true)
            ->andThrows(new \YapepBase\Exception\File\Exception('Create Failed'));
    }

    protected function expectWriteFails()
    {
        $this->fileHandler
            ->shouldReceive('write')
            ->once()
            ->andThrows(new Exception('Test'));
    }
}
