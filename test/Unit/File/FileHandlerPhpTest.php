<?php

namespace YapepBase\Test\Unit\File;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use vendor\project\StatusTest;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\File\Exception\Exception;
use YapepBase\File\Exception\NotFoundException;
use YapepBase\File\FileHandlerPhp;
use YapepBase\Test\Unit\TestAbstract;

class FileHandlerPhpTest extends TestAbstract
{
    /** @var vfsStreamDirectory */
    private $root;
    /** @var string */
    private $childName = 'test';
    /** @var string */
    private $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup();
        $this->path = $this->root->url() . '/' . $this->childName;
    }

    public function testTouch_shouldCreateFile()
    {
        $modificationTime = time();
        $accessTime = time() + 1;

        $this->getFileHandler()->touch($this->path, $modificationTime, $accessTime);

        $this->assertTrue($this->root->hasChild($this->childName));
        $this->assertStatSame('mtime', $modificationTime);
        $this->assertStatSame('atime', $accessTime);
    }

    public function testMakeDirectory_shouldCreateDirectoryWithGivenRights()
    {
        $this->getFileHandler()->makeDirectory($this->path);

        $this->assertTrue($this->root->hasChild($this->childName));
    }

    public function testWrite_shouldWriteGivenContent()
    {
        $data = 'data';
        $this->getFileHandler()->write($this->path, $data);

        $this->assertTrue($this->root->hasChild($this->childName));
        $this->assertSame($data, $this->root->getChild($this->childName)->getContent());
    }

    public function testChangeOwnerWhenPathDoesNotExist_shouldThrowException()
    {
        $this->expectException(Exception::class);
        $this->getFileHandler()->changeOwner($this->path);
    }

    public function testChangeModeWhenPathDoesNotExist_shouldThrowException()
    {
        $this->expectException(NotFoundException::class);
        $this->getFileHandler()->changeMode($this->path, 0555);
    }

    public function testChangeMode_shouldChangeMode()
    {
        $this->getFileHandler()->touch($this->path);

        $this->getFileHandler()->changeMode($this->path, 0777);
        $this->assertStatSame('mode', 33279);
    }

    public function testCopyWhenSourceDoesNotExist_shouldThrowException()
    {
        $destination = $this->root->url() . '/destination';

        $this->expectException(NotFoundException::class);
        $this->getFileHandler()->copy($this->path, $destination);
    }

    public function testCopy_shouldCopy()
    {
        $this->getFileHandler()->touch($this->path);
        $destinationChildName = 'destination';
        $destination          = $this->root->url() . '/' . $destinationChildName;

        $this->getFileHandler()->copy($this->path, $destination);

        $this->assertTrue($this->root->hasChild($destinationChildName));
        $this->assertTrue($this->root->hasChild($this->childName));
    }

    public function testRemoveWhenDirectoryGiven_shouldThrowException()
    {
        $fileHandler = $this->getFileHandler();
        $fileHandler->makeDirectory($this->path);

        $this->expectException(Exception::class);
        $fileHandler->remove($this->path);
    }

    public function testRemoveWhenPathDoesNotExist_shouldNotDoAnything()
    {
        $fileHandler = $this->getFileHandler();

        $fileHandler->remove($this->path);

        $this->assertTrue(true);
    }

    public function testRemove_shouldRemoveFile()
    {
        $fileHandler = $this->getFileHandler();
        $fileHandler->touch($this->path);

        $fileHandler->remove($this->path);

        $this->assertFalse($this->root->hasChild($this->childName));
    }

    public function testRemoveDirectorWhenPathDoesNotExist_shouldNotDoAnything()
    {
        $fileHandler = $this->getFileHandler();

        $fileHandler->removeDirectory($this->path);

        $this->assertTrue(true);
    }

    public function testRemoveDirectoryWhenFileGiven_shouldThrowException()
    {
        $fileHandler = $this->getFileHandler();
        $fileHandler->touch($this->path);

        $this->expectException(Exception::class);
        $fileHandler->removeDirectory($this->path);
    }

    public function testRemoveDirectoryWhenHasChildrenButNotRecursive_shouldThrowException()
    {
        $fileHandler = $this->getFileHandler();
        $fileHandler->makeDirectory($this->path);
        $fileHandler->touch($this->path . '/test');

        $this->expectException(Exception::class);
        $fileHandler->removeDirectory($this->path);
    }

    public function testRemoveDirectory_shouldRemove()
    {
        $fileHandler = $this->getFileHandler();
        $fileHandler->makeDirectory($this->path);
        $fileHandler->touch($this->path . '/test');

        $fileHandler->removeDirectory($this->path, true);

        $this->assertFalse($this->root->hasChild($this->childName));
    }

    public function testMoveWhenSourceDoesNotExist_shouldThrowException()
    {
        $this->expectException(NotFoundException::class);
        $this->getFileHandler()->move($this->path, 'destination');
    }

    public function testMove_shouldMoveFile()
    {
        $this->getFileHandler()->touch($this->path);
        $destinationChildName = 'destination';
        $destination          = $this->root->url() . '/' . $destinationChildName;

        $this->getFileHandler()->move($this->path, $destination);

        $this->assertTrue($this->root->hasChild($destinationChildName));
        $this->assertFalse($this->root->hasChild($this->childName));
    }

    public function testGetParentDirectory_shouldReturnParentOfGivenPath()
    {
        $result = $this->getFileHandler()->getParentDirectory($this->path);
        $this->assertSame('vfs://root', $result);
    }

    public function testGetCurrentDirectory_shouldReturn()
    {
        $result = $this->getFileHandler()->getCurrentDirectory();
        $this->assertSame(getcwd(), $result);
    }

    public function testGetAsStringNegativeMaxLengthGiven_shouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getFileHandler()->getAsString($this->path, 0, -1);
    }

    public function testGetAsStringWhenMaxLengthGiven_shouldOnlyReturnGivenLength()
    {
        $fileHandler = $this->getFileHandler();

        $fileHandler->touch($this->path);
        $fileHandler->write($this->path, 'test');

        $result = $fileHandler->getAsString($this->path, 0, 2);

        $this->assertSame('te', $result);
    }

    public function testGetAsStringWhenOffsetGiven_shouldOnlyReturnFromOffset()
    {
        $fileHandler = $this->getFileHandler();

        $fileHandler->touch($this->path);
        $fileHandler->write($this->path, 'test');

        $result = $fileHandler->getAsString($this->path, 2);

        $this->assertSame('st', $result);
    }

    public function testGetAsString_shouldReturnContent()
    {
        $fileHandler = $this->getFileHandler();
        $fileContent = 'test';

        $fileHandler->touch($this->path);
        $fileHandler->write($this->path, $fileContent);

        $result = $fileHandler->getAsString($this->path);

        $this->assertSame($fileContent, $result);
    }

    public function testGetListWhenNotDirectory_shouldThrowException()
    {

    }

    private function assertStatSame(string $statName, $expected): void
    {
        $fileStat = stat($this->path);
        $this->assertSame($expected, $fileStat[$statName]);
    }

    private function getFileHandler(): FileHandlerPhp
    {
        return new FileHandlerPhp();
    }
}
