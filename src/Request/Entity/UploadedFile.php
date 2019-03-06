<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

use YapepBase\Application;

/**
 * Data class for holding uploaded file data.
 */
class UploadedFile
{
    /** @var string */
    protected $filename;

    /** @var int */
    protected $sizeInByte;

    /** @var string */
    protected $temporaryFilePath;

    /** @var string|null */
    protected $mimeType;

    /** @var int */
    protected $errorCode;

    public function __construct(string $filename, int $sizeInByte, string $tempFilePath, int $errorCode, ?string $mimeType)
    {
        $this->filename          = $filename;
        $this->sizeInByte        = $sizeInByte;
        $this->temporaryFilePath = $tempFilePath;
        $this->mimeType          = $mimeType;
        $this->errorCode         = $errorCode;

        $this->correctSizeIfNeeded();
    }

    /**
     * If the filesize is smaller than 0, we have a file that's bigger than 2GB.
     */
    protected function correctSizeIfNeeded()
    {
        $fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

        if (
            $this->sizeInByte < 0
            && $fileHandler->checkIsPathExists($this->temporaryFilePath)
        ) {
            $this->sizeInByte = $fileHandler->getSize($this->temporaryFilePath);
        }
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFileExtension(): ?string
    {
        $filename = $this->getFilename();

        if (preg_match('/\.([a-zA-Z0-9]+)$/', $filename, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    public function getSizeInByte(): int
    {
        return $this->sizeInByte;
    }

    public function getTemporaryFilePath(): string
    {
        return $this->temporaryFilePath;
    }

    /**
     * Do not trust this data, as it is provided by the client and not verified to be correct!
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getFileContent(): string
    {
        return file_get_contents($this->getTemporaryFilePath());
    }

    /**
     * @see http://php.net/manual/en/features.file-upload.errors.php
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function hasError(): bool
    {
        return $this->errorCode === UPLOAD_ERR_OK
            ? false
            : true;
    }
}
