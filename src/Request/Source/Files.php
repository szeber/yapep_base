<?php
declare(strict_types=1);

namespace YapepBase\Request\Source;

use YapepBase\Exception\ParameterException;

/**
 * Stores and handles the uploaded files
 */
class Files implements IFiles
{
    const KEY_FILENAME       = 'name';
    const KEY_ERROR_CODE     = 'error';
    const KEY_SIZE           = 'size';
    const KEY_TEMP_FILE_PATH = 'tmp_name';
    const KEY_MIME_TYPE      = 'type';

    /** @var array */
    protected $filesByName = [];

    /** @var File[][] */
    protected $fileObjectsByNameAndIndex = [];

    /**
     * @param array $filesByName
     */
    public function __construct(array $filesByName)
    {
        $this->filesByName = $filesByName;

        $transformedFiles = $this->getTransformedFiles();

        foreach ($transformedFiles as $name => $files) {
            foreach ($files as $index => $file) {
                $this->validateFile($file);
                $this->fileObjectsByNameAndIndex[$name][$index] = $this->getFile($file);
            }
        }
    }

    /**
     * Though PHPs $_FILES indexing for multiple upload is just awesome and logical, it's easier to process if it's restructured
     *
     * This method replaces the original structure (For example  $_FILES['image'][0]['name'])
     * to  $_FILES['image']['name'][0].
     */
    protected function getTransformedFiles(): array
    {
        $filesByNameAndIndex = [];

        foreach ($this->filesByName as $name => $file) {
            $this->validateFile($file);

            if (is_array($file[self::KEY_FILENAME])) {
                foreach ($file[self::KEY_FILENAME] as $index => $value) {
                    $transformedFile = [];
                    $this->populateValueFromMultiple($file, $index, self::KEY_FILENAME, $transformedFile);
                    $this->populateValueFromMultiple($file, $index, self::KEY_ERROR_CODE, $transformedFile);
                    $this->populateValueFromMultiple($file, $index, self::KEY_SIZE, $transformedFile);
                    $this->populateValueFromMultiple($file, $index, self::KEY_TEMP_FILE_PATH, $transformedFile);
                    $this->populateValueFromMultiple($file, $index, self::KEY_MIME_TYPE, $transformedFile);

                    $filesByNameAndIndex[$name][$index] = $transformedFile;
                }
            }
            else {
                $filesByNameAndIndex[$name][0] = $file;
            }
        }

        return $filesByNameAndIndex;
    }

    protected function populateValueFromMultiple(array $files, int $index, string $key, array &$output): void
    {
        if (isset($files[$key][$index])) {
            $output[$key] = $files[$key][$index];
        }
    }

    /**
     * @throws ParameterException
     */
    protected function validateFile(array $file): void
    {
        if (
            !isset($file[self::KEY_FILENAME])
            || !isset($file[self::KEY_ERROR_CODE])
            || !isset($file[self::KEY_SIZE])
            || (empty($file[self::KEY_TEMP_FILE_PATH]) && $file[self::KEY_ERROR_CODE] == UPLOAD_ERR_OK)
        ) {
            throw new ParameterException('Invalid array provided. Some required fields are missing');
        }
    }

    protected function getFile(array $file): File
    {
        $filename          = $file[self::KEY_FILENAME];
        $sizeInByte        = (int)$file[self::KEY_SIZE];
        $temporaryFilePath = $file[self::KEY_TEMP_FILE_PATH];
        $errorCode         = (int)$file[self::KEY_ERROR_CODE];
        $mimeType          = isset($file[self::KEY_MIME_TYPE]) ? $file[self::KEY_MIME_TYPE] : null;

        return new File($filename, $sizeInByte, $temporaryFilePath, $errorCode, $mimeType);
    }

    public function isMultiUpload(string $name): bool
    {
        if (!$this->has($name)) {
            throw new ParameterException('File ' . $name . ' does not exist');
        }
        return count($this->fileObjectsByNameAndIndex[$name]) > 1;
    }

    public function get(string $name, int $index = 0): ?File
    {
        return isset($this->fileObjectsByNameAndIndex[$name][$index])
            ? $this->fileObjectsByNameAndIndex[$name][$index]
            : null;
    }

    public function has(string $name): bool
    {
        return isset($this->fileObjectsByNameAndIndex[$name]);
    }

    public function toArray(): array
    {
        return $this->fileObjectsByNameAndIndex;
    }

    public function getOriginal(): array
    {
        return $this->filesByName;
    }
}
