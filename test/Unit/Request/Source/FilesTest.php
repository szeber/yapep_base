<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Request\Source;

use PHPUnit\Framework\ExpectationFailedException;
use YapepBase\Exception\ParameterException;
use YapepBase\Request\Source\File;
use YapepBase\Request\Source\Files;
use YapepBase\Test\Unit\TestAbstract;

class FilesTest extends TestAbstract
{
    /** @var string */
    protected $paramName = 'param';

    /** @var string */
    protected $filename1 = 'file.ext';
    /** @var int */
    protected $size1    = 1;
    /** @var string */
    protected $tmpName1 = 'tmp';
    /** @var int */
    protected $error1   = UPLOAD_ERR_OK;
    /** @var string */
    protected $type1    = 'text';

    /** @var string */
    protected $filename2 = 'file2.ext';
    /** @var int */
    protected $size2    = 2;
    /** @var string */
    protected $tmpName2 = 'tmp2';
    /** @var int */
    protected $error2   = UPLOAD_ERR_CANT_WRITE;
    /** @var string */
    protected $type2    = 'text2';

    public function invalidFileProvider(): array
    {
        return [
            'no name'      => [[Files::KEY_ERROR_CODE => 0, Files::KEY_SIZE => 1, Files::KEY_TEMP_FILE_PATH => 'tmp']],
            'no error'     => [[Files::KEY_FILENAME => 'name', Files::KEY_SIZE => 1, Files::KEY_TEMP_FILE_PATH => 'tmp']],
            'no size'      => [[Files::KEY_FILENAME => 'name', Files::KEY_ERROR_CODE => 0, Files::KEY_TEMP_FILE_PATH => 'tmp']],
            'no temp path' => [[Files::KEY_FILENAME => 'name', Files::KEY_SIZE => 1, Files::KEY_ERROR_CODE => UPLOAD_ERR_OK]],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testConstructWhenInvalidFileGiven_shouldThrowException(array $invalidFile)
    {
        $this->expectException(ParameterException::class);
        new Files(['invalid' => $invalidFile]);
    }

    public function testIsMultiUploadWhenNameDoesNotExist_shouldThrowException()
    {
        $files = new Files([]);

        $this->expectException(ParameterException::class);
        $files->isMultiUpload('nonExistent');
    }

    public function testIsMultiUploadWhenOneFileGiven_shouldReturnFalse()
    {
        $file  = $this->getFile();
        $files = new Files([$this->paramName => $file]);

        $isMultiUpload = $files->isMultiUpload($this->paramName);

        $this->assertFalse($isMultiUpload);
    }

    public function testIsMultiUploadWhenTwoFilesGiven_shouldReturnTrue()
    {
        $multiFile = $this->getMultiFile();
        $files     = new Files([$this->paramName => $multiFile]);

        $isMultiUpload = $files->isMultiUpload($this->paramName);

        $this->assertTrue($isMultiUpload);
    }

    public function testGetWhenNameAndIndexExists_shouldReturnProperlyConfiguredFileObject()
    {
        $file  = $this->getFile();
        $files = new Files([$this->paramName => $file]);

        $result = $files->get($this->paramName);

        $this->assertFileObjectEquals($this->getFileObject1(), $result);
    }

    public function testGetWhenNameExistsButIndexNot_shouldReturnNull()
    {
        $file  = $this->getFile();
        $files = new Files([$this->paramName => $file]);

        $result = $files->get($this->paramName, 1);

        $this->assertNull($result);
    }

    public function testGetWhenMultipleFileUploaded_shouldReturnProperlyConfiguredFileObject()
    {
        $multiFile = $this->getMultiFile();
        $files     = new Files([$this->paramName => $multiFile]);

        $result = $files->get($this->paramName, 1);

        $this->assertFileObjectEquals($this->getFileObject2(), $result);
    }

    public function testHasWhenNameExists_shouldReturnTrue()
    {
        $file  = $this->getFile();
        $files = new Files([$this->paramName => $file]);

        $result = $files->has($this->paramName);

        $this->assertTrue($result);
    }

    public function testHasWhenNameNotExists_shouldReturnFalse()
    {
        $file  = $this->getFile();
        $files = new Files([$this->paramName => $file]);

        $result = $files->has('nonExistent');

        $this->assertFalse($result);
    }

    public function testToArray_shouldReturnObjectsByNameAndIndex()
    {
        $multiFile = $this->getMultiFile();
        $files     = new Files([$this->paramName => $multiFile]);

        $result = $files->toArray();

        $expectedResult = [
            $this->paramName => [
                $this->getFileObject1(),
                $this->getFileObject2(),
            ],
        ];

        $this->assertArrayOfFileObjectsEquals($expectedResult, $result);
    }

    public function testToOriginal_shouldReturnOriginalPassedArray()
    {
        $filesArray = [$this->paramName => $this->getMultiFile()];
        $files      = new Files($filesArray);

        $result = $files->getOriginal();

        $this->assertSame($filesArray, $result);
    }

    /**
     * @throws ExpectationFailedException
     */
    protected function assertFileObjectEquals(File $expectedFile, File $actualFile): void
    {
        $this->assertEquals($expectedFile->getFilename(), $actualFile->getFilename());
        $this->assertEquals($expectedFile->getSizeInByte(), $actualFile->getSizeInByte());
        $this->assertEquals($expectedFile->getTemporaryFilePath(), $actualFile->getTemporaryFilePath());
        $this->assertEquals($expectedFile->getErrorCode(), $actualFile->getErrorCode());
        $this->assertEquals($expectedFile->getMimeType(), $actualFile->getMimeType());
    }

    protected function assertArrayOfFileObjectsEquals(array $expectedFiles, array $actualFiles)
    {
        $toArrayFunction = function (File &$item) {
            $item = $item->toArray();
        };

        array_walk_recursive($expectedFiles, $toArrayFunction);
        array_walk_recursive($actualFiles, $toArrayFunction);

        $this->assertSame($expectedFiles, $actualFiles);
    }

    protected function getFile(): array
    {
        return [
            Files::KEY_FILENAME       => $this->filename1,
            Files::KEY_SIZE           => $this->size1,
            Files::KEY_TEMP_FILE_PATH => $this->tmpName1,
            Files::KEY_ERROR_CODE     => $this->error1,
            Files::KEY_MIME_TYPE      => $this->type1,
        ];
    }

    protected function getMultiFile(): array
    {
        return [
            Files::KEY_FILENAME       => [$this->filename1, $this->filename2],
            Files::KEY_SIZE           => [$this->size1, $this->size2],
            Files::KEY_TEMP_FILE_PATH => [$this->tmpName1, $this->tmpName2],
            Files::KEY_ERROR_CODE     => [$this->error1, $this->error2],
            Files::KEY_MIME_TYPE      => [$this->type1, $this->type2],
        ];
    }

    protected function getFileObject1(): File
    {
        return new File($this->filename1, $this->size1, $this->tmpName1, $this->error1, $this->type1);
    }

    protected function getFileObject2(): File
    {
        return new File($this->filename2, $this->size2, $this->tmpName2, $this->error2, $this->type2);
    }
}
