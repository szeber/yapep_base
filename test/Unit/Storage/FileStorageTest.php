<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Storage;

use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\File\Exception;
use YapepBase\Exception\File\NotFoundException;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Exception\StorageException;
use YapepBase\Storage\Entity\File;
use YapepBase\Storage\FileStorage;

class FileStorageTest extends TestAbstract
{
    /** @var FileHandlerMock */
    protected $fileHandler;
    /** @var \YapepBase\Storage\Key\Generator */
    protected $keyGenerator;
    /** @var int */
    protected $fileModeOctal = 0666;
    /** @var string */
    protected $path = '/tmp/path/';
    /** @var string */
    protected $pathWithoutTrailingSlash = '/tmp/path';
    /** @var string */
    protected $key = 'key1';
    /** @var string */
    protected $fullPath = '/tmp/path/key1';
    /** @var array */
    protected $data = ['test'];
    /** @var int */
    protected $ttl = 2;
    /** @var int */
    protected $createdAt = 10;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHandler  = new FileHandlerMock($this->fileModeOctal, $this->path, $this->pathWithoutTrailingSlash, $this->fullPath);
        $this->keyGenerator = new \YapepBase\Storage\Key\Generator(false);

        $this->initDebugDataHandler();
    }

    public function testConstructWhenPathEmpty_shouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getStorageForConstructTest('');
    }

    public function testConstructWhenPathNotExistAndCantCreate_shouldThrowException()
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, false)
            ->expectMakeDirectoryFails();
        $this->expectException(StorageException::class);

        $this->getStorageForConstructTest($this->path);
    }

    public function testConstructWhenPathIsNotDirectory_shouldThrowException()
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(false);
        $this->expectException(StorageException::class);

        $this->getStorageForConstructTest($this->pathWithoutTrailingSlash);
    }

    public function testConstructWhenPathIsNotWritable_shouldThrowException()
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(true)
            ->expectCheckIsWritable(false);
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
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(true);
        $storage = $this->getStorageForConstructTest($this->path, true);

        $this->expectException(StorageException::class);
        $storage->set($this->key, $this->data);
    }

    public function testSetWhenStoreFails_shouldThrowException()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->expectGetCreatedAt();
        $this->fileHandler->expectWriteFails();

        $this->expectException(StorageException::class);
        $storage->set($this->key, $this->data, $this->ttl);
    }

    public function testSetWhenTtlGivenInPlainTextMode_shouldThrowException()
    {
        $storage = $this->getStorage($this->path);
        $storage->setCanOnlyStorePlainText(true);

        $this->expectDebugTimesRetrieved();

        $this->expectException(InvalidArgumentException::class);
        $storage->set($this->key, $this->data, $this->ttl);
    }

    public function testSetWhenInPlainTextMode_shouldStorePlainText()
    {
        $plainText = 'test';
        $storage   = $this->getStorage($this->path);
        $storage->setCanOnlyStorePlainText(true);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler->expectWrite($plainText);
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $plainText, $this->debugStartedAt, $this->debugFinishedAt);

        $storage->set($this->key, $plainText);
    }

    public function setTtlProvider()
    {
        return [
            'not empty ttl' => [$this->ttl, $this->createdAt + $this->ttl],
            'empty ttl'     => [0, 0],
        ];
    }

    /**
     * @dataProvider setTtlProvider
     */
    public function testSet_shouldStoreJsonEncodedText(int $ttl, int $expiresAt)
    {
        $storage            = $this->getStorage($this->path);
        $expectedStoredData = new File($this->data, $this->createdAt, $expiresAt);

        $this->expectGetCreatedAt();
        $this->expectDebugTimesRetrieved();
        $this->fileHandler->expectWrite(json_encode($expectedStoredData));
        $this->expectAddStorageDebug(Storage::METHOD_SET, $this->key, $this->data, $this->debugStartedAt, $this->debugFinishedAt);

        $storage->set($this->key, $this->data, $ttl);
    }

    public function testGetWhenFileDoesNotExist_shouldReturnNull()
    {
        $fileStorage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler->expectCheckPathExists($this->fullPath, false);
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, null, $this->debugStartedAt, $this->debugFinishedAt);
        $result = $fileStorage->get($this->key);

        $this->assertNull($result);
    }

    public function testGetWhenFileNotReadable_shouldThrowException()
    {
        $fileStorage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler
            ->expectCheckPathExists($this->fullPath, true)
            ->expectCheckIsFileReadable(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Unable to read file');
        $fileStorage->get($this->key);
    }

    public function testGetWhenCantGetFileContent_shouldThrowException()
    {
        $fileStorage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler
            ->expectCheckPathExists($this->fullPath, true)
            ->expectCheckIsFileReadable(true)
            ->expectGetFileContent(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Unable to read file');
        $fileStorage->get($this->key);
    }

    public function testGetWhenExpired_shouldRemoveFileAndReturnNull()
    {
        $expiresAt   = 20;
        $currentTime = 22;
        $fileStorage = $this->getStorage($this->path);
        $file        = new File($this->data, $this->createdAt, $expiresAt);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler
            ->expectCheckPathExists($this->fullPath, true)
            ->expectCheckIsFileReadable(true)
            ->expectGetFileContent(json_encode($file))
            ->expectRemoveFile();
        $this->expectGetCurrentTime($currentTime);
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, null, $this->debugStartedAt, $this->debugFinishedAt);

        $result = $fileStorage->get($this->key);

        $this->assertNull($result);
    }

    public function testGet_shouldReturnStoredData()
    {
        $expiresAt   = 20;
        $currentTime = 18;
        $fileStorage = $this->getStorage($this->path);
        $file        = new \YapepBase\Storage\Entity\File($this->data, $this->createdAt, $expiresAt);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler
            ->expectCheckPathExists($this->fullPath, true)
            ->expectCheckIsFileReadable(true)
            ->expectGetFileContent(json_encode($file));
        $this->expectGetCurrentTime($currentTime);
        $this->expectAddStorageDebug(Storage::METHOD_GET, $this->key, $this->data, $this->debugStartedAt, $this->debugFinishedAt);

        $result = $fileStorage->get($this->key);

        $this->assertEquals($this->data, $result);
    }

    public function testDeleteWhenReadOnly_shouldThrowException()
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(true);
        $storage = $this->getStorageForConstructTest($this->path, true);

        $this->expectException(StorageException::class);
        $storage->delete($this->key);
    }

    public function testDeleteWhenRemoveFails_shouldThrowException()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler->expectRemoveFileFails(new Exception('Error'));

        $this->expectException(StorageException::class);
        $storage->delete($this->key);
    }

    public function testDeleteWhenFileDoesNotExist_shouldJustCreateDebugEntry()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->expectAddStorageDebug(Storage::METHOD_DELETE, $this->key, null, $this->debugStartedAt, $this->debugFinishedAt);
        $this->fileHandler->expectRemoveFileFails(new NotFoundException('File not found'));

        $storage->delete($this->key);
    }

    public function testDelete_shouldRemoveFile()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->expectAddStorageDebug(Storage::METHOD_DELETE, $this->key, null, $this->debugStartedAt, $this->debugFinishedAt);
        $this->fileHandler->expectRemoveFile();

        $storage->delete($this->key);
    }

    public function testClearWhenReadOnly_shouldThrowException()
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(true);
        $storage = $this->getStorageForConstructTest($this->path, true);

        $this->expectException(StorageException::class);
        $storage->clear();
    }

    public function testClearWhenRemoveFails_shouldThrowException()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->fileHandler->expectRemoveDirectoryFails();

        $this->expectException(StorageException::class);
        $storage->clear();
    }

    public function testClear_shouldRemoveContainerDirectory()
    {
        $storage = $this->getStorage($this->path);

        $this->expectDebugTimesRetrieved();
        $this->expectAddStorageDebug(Storage::METHOD_CLEAR, null, null, $this->debugStartedAt, $this->debugFinishedAt);
        $this->fileHandler->expectRemoveDirectory();

        $storage->clear();
    }

    protected function getStorageForConstructTest(string $path, bool $readOnly = false): FileStorage
    {
        return new FileStorage($this->fileHandler->getMock(), $this->keyGenerator, $this->dateHelper, $path, $readOnly);
    }

    protected function getStorage(string $path, bool $readOnly = false): FileStorage
    {
        $this->fileHandler
            ->expectCheckPathExists($this->path, true)
            ->expectCheckIsDirectory(true)
            ->expectCheckIsWritable(true);

        $fileStorage = new FileStorage($this->fileHandler->getMock(), $this->keyGenerator, $this->dateHelper, $path, $readOnly);
        $fileStorage->setFileModeOctal($this->fileModeOctal);

        return $fileStorage;
    }

    protected function expectGetCreatedAt()
    {
        $this->dateHelper
            ->shouldReceive('getCurrentTimestamp')
            ->once()
            ->andReturn($this->createdAt);
    }

    protected function expectGetCurrentTime(int $expectedResult)
    {
        $this->dateHelper
            ->shouldReceive('getCurrentTimestamp')
            ->once()
            ->andReturn($expectedResult);
    }
}
