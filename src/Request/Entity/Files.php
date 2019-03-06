<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

use YapepBase\Exception\ParameterException;

/**
 * Stores and handles the uploaded files
 */
class Files implements IFiles
{
    /** @var array */
    protected $filesByName = [];

    /** @var UploadedFile[] */
    protected $fileObjectsByName = [];

    /**
     * @param array $filesByName
     */
    public function __construct(array $filesByName)
    {
        $this->filesByName = $filesByName;

        foreach ($filesByName as $name => $file) {
            $this->validateFile($filesByName);
            $this->fileObjectsByName[$name] = $this->getFile($file);
        }
    }

    /**
     * @throws ParameterException
     */
    protected function validateFile(array $file): void
    {
        if (
            !isset($file['name'])
            || !isset($file['error'])
            || !isset($file['size'])
            || (empty($file['tmp_name']) && $file['error'] == UPLOAD_ERR_OK)
        ) {
            throw new ParameterException('Invalid array provided. Some required fields are missing');
        }
    }

    protected function getFile(array $file): UploadedFile
    {
        $filename          = $file['name'];
        $sizeInByte        = (int)$file['size'];
        $temporaryFilePath = $file['tmp_name'];
        $errorCode         = (int)$file['error'];
        $mimeType          = isset($file['type']) ? $file['type'] : null;

        return new UploadedFile($filename, $sizeInByte, $temporaryFilePath, $errorCode, $mimeType);
    }

    public function isMultiUpload(): bool
    {
        return count($this->fileObjectsByName) > 1;
    }

    public function get(string $name): ?UploadedFile
    {
        return isset($this->fileObjectsByName[$name])
            ? $this->fileObjectsByName[$name]
            : null;
    }

    public function has(string $name): bool
    {
        return isset($this->fileObjectsByName[$name]);
    }

    public function toArray(): array
    {
        return $this->fileObjectsByName;
    }
}
