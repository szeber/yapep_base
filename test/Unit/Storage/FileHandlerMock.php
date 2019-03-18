<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\File\IFileHandler;

class FileHandlerMock
{
    /** @var MockInterface */
    public $fileHandler;

    /** @var int */
    private $fileModeOctal;
    /** @var string */
    private $path;
    /** @var string */
    private $pathWithoutTrailingSlash;
    /** @var string */
    private $fullPath;

    public function __construct(int $fileModeOctal, string $path, string $pathWithoutTrailingSlash, string $fullPath)
    {
        $this->fileModeOctal            = $fileModeOctal;
        $this->path                     = $path;
        $this->pathWithoutTrailingSlash = $pathWithoutTrailingSlash;
        $this->fullPath                 = $fullPath;
        $this->fileHandler              = \Mockery::mock(IFileHandler::class);
    }

    public function getMock(): IFileHandler
    {
        return $this->fileHandler;
    }

    public function expectCheckPathExists(string $path, bool $expectedResult): self
    {
        $this->fileHandler
            ->shouldReceive('checkIsPathExists')
            ->once()
            ->with($path)
            ->andReturn($expectedResult);

        return $this;
    }

    public function expectCheckIsDirectory(bool $expectedResult): self
    {
        $this->fileHandler
            ->shouldReceive('checkIsDirectory')
            ->once()
            ->with($this->pathWithoutTrailingSlash)
            ->andReturn($expectedResult);

        return $this;
    }

    public function expectCheckIsWritable(bool $expectedResult): self
    {
        $this->fileHandler
            ->shouldReceive('checkIsWritable')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);

        return $this;
    }

    public function expectMakeDirectoryFails(): self
    {
        $this->fileHandler
            ->shouldReceive('makeDirectory')
            ->once()
            ->with($this->path, 0644 | 0111, true)
            ->andThrows(new \YapepBase\Exception\File\Exception('Create Failed'));

        return $this;
    }

    public function expectWriteFails(): self
    {
        $this->fileHandler
            ->shouldReceive('write')
            ->once()
            ->andThrows(new \YapepBase\Exception\File\Exception('Test'));

        return $this;
    }

    public function expectWrite($storedData): self
    {
        $this->fileHandler
            ->shouldReceive('write')
                ->once()
                ->with($this->fullPath, $storedData)
                ->getMock()
            ->shouldReceive('changeMode')
                ->once()
                ->with($this->fullPath, $this->fileModeOctal);

        return $this;
    }

    public function expectCheckIsFileReadable(bool $expectedResult): self
    {
        $this->fileHandler
            ->shouldReceive('checkIsReadable')
            ->once()
            ->with($this->fullPath)
            ->andReturn($expectedResult);

        return $this;
    }

    public function expectGetFileContent($expectedResult): self
    {
        $this->fileHandler
            ->shouldReceive('getAsString')
            ->once()
            ->with($this->fullPath)
            ->andReturn($expectedResult);

        return $this;
    }

    public function expectRemoveFile(): self
    {
        $this->fileHandler
            ->shouldReceive('remove')
            ->once()
            ->with($this->fullPath);

        return $this;
    }

    public function expectRemoveFileFails($expectedException): self
    {
        $this->fileHandler
            ->shouldReceive('remove')
            ->once()
            ->with($this->fullPath)
            ->andThrows($expectedException);

        return $this;
    }

    public function expectRemoveDirectory(): self
    {
        $this->fileHandler
            ->shouldReceive('removeDirectory')
            ->once()
            ->with($this->path, true);

        return $this;
    }

    public function expectRemoveDirectoryFails(): self
    {
        $this->fileHandler
            ->shouldReceive('removeDirectory')
            ->once()
            ->with($this->path, true)
            ->andThrows(new \YapepBase\Exception\File\Exception('test'));

        return $this;
    }
}
