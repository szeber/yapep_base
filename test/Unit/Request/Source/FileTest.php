<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Request\Source;

use YapepBase\File\IFileHandler;
use YapepBase\Request\Source\File;
use YapepBase\Request\Source\Files;
use YapepBase\Test\Unit\TestAbstract;
use Mockery;

class FileTest extends TestAbstract
{
    /** @var string */
    protected $filename = 'filename.txt';
    /** @var int */
    protected $sizeInByte = 12;
    /** @var string */
    protected $tempFilePath = '/tmp/uploaded.file';
    /** @var int */
    protected $errorCode = 0;
    /** @var null */
    protected $mimeType = 'test/test';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testConstruct_shouldGetActualFilesizeIsNegative()
    {
        $expectedSizeInByte = 24;
        $this->expectGetFileSize($expectedSizeInByte);

        $file = new File($this->filename, -1, $this->tempFilePath, $this->errorCode, $this->mimeType);
        $sizeInByte = $file->getSizeInByte();

        $this->assertSame($expectedSizeInByte, $sizeInByte);
    }

    public function testGetExtension_shouldReturnFileExtension()
    {
        $extension = $this->getFile()->getFileExtension();

        $this->assertSame('txt', $extension);
    }

    public function testGetFilename_shouldReturnSetValue()
    {
        $filename = $this->getFile()->getFilename();

        $this->assertSame($this->filename, $filename);
    }

    public function testGetSizeInByte_shouldReturnSetValue()
    {
        $sizeInByte = $this->getFile()->getSizeInByte();

        $this->assertSame($this->sizeInByte, $sizeInByte);
    }

    public function testGetTemporaryFilePath_shouldReturnSetValue()
    {
        $tempPath = $this->getFile()->getTemporaryFilePath();

        $this->assertSame($this->tempFilePath, $tempPath);
    }

    public function testGetErrorCode_shouldReturnSetValue()
    {
        $errorCode = $this->getFile()->getErrorCode();

        $this->assertSame($this->errorCode, $errorCode);
    }

    public function testGetMimeType_shouldReturnSetValue()
    {
        $mimType = $this->getFile()->getMimeType();

        $this->assertSame($this->mimeType, $mimType);
    }

    public function testGetFileContent_shouldReturnFileContent()
    {
        $expectedContent = 'Content';

        $this->expectGetFileContent($expectedContent);

        $content = $this->getFile()->getFileContent();

        $this->assertSame($expectedContent, $content);
    }

    public function errorCodeProvider()
    {
        return [
            'OK'    => [UPLOAD_ERR_OK, false],
            'Error' => [UPLOAD_ERR_CANT_WRITE, true],
        ];
    }

    /**
     * @dataProvider errorCodeProvider
     */
    public function testHasError_shouldReturnFalseWhenUploadWasOk(int $errorCode, bool $expectedResult)
    {
        $file = new File($this->filename, $this->sizeInByte, $this->tempFilePath, $errorCode, $this->mimeType);

        $hasError = $file->hasError();

        $this->assertSame($expectedResult, $hasError);
    }

    public function testToArray_shouldReturnArrayInPhpFilesStructure()
    {
        $array = $this->getFile()->toArray();

        $expectedArray = [
            Files::KEY_FILENAME       => $this->filename,
            Files::KEY_SIZE           => $this->sizeInByte,
            Files::KEY_TEMP_FILE_PATH => $this->tempFilePath,
            Files::KEY_ERROR_CODE     => $this->errorCode,
            Files::KEY_MIME_TYPE      => $this->mimeType
        ];

        $this->assertSame($expectedArray, $array);
    }

    protected function getFile(): File
    {
        return new File($this->filename, $this->sizeInByte, $this->tempFilePath, $this->errorCode, $this->mimeType);
    }

    protected function expectGetFileSize(int $expectedSize)
    {
        $fileHandlerMock = Mockery::mock(IFileHandler::class)
            ->shouldReceive('checkIsPathExists')
                ->once()
                ->with($this->tempFilePath)
                ->andReturn(true)
                ->getMock()
            ->shouldReceive('getSize')
                ->once()
                ->with($this->tempFilePath)
                ->andReturn($expectedSize)
                ->getMock();

        $this->pimpleContainer->setFileHandler($fileHandlerMock);
    }

    protected function expectGetFileContent(string $expectedContent)
    {
        $fileHandlerMock = Mockery::mock(IFileHandler::class)
            ->shouldReceive('getAsString')
                ->once()
                ->with($this->tempFilePath)
                ->andReturn($expectedContent)
                ->getMock();

        $this->pimpleContainer->setFileHandler($fileHandlerMock);
    }
}
