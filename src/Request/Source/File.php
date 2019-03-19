<?php
declare(strict_types=1);

namespace YapepBase\Request\Source;

use YapepBase\Application;

/**
 * Data class for holding uploaded file data.
 */
class File
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
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFileExtension(): ?string
    {
        $extension = pathinfo($this->getFilename(), PATHINFO_EXTENSION);

        return empty($extension)
            ? null
            : $extension;
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
        $fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

        return $fileHandler->getAsString($this->getTemporaryFilePath());
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

    /**
     * Returns an array in the same structure as PHP populates the $_FILES array.
     */
    public function toArray(): array
    {
        return [
            Files::KEY_FILENAME       => $this->filename,
            Files::KEY_SIZE           => $this->sizeInByte,
            Files::KEY_TEMP_FILE_PATH => $this->temporaryFilePath,
            Files::KEY_ERROR_CODE     => $this->errorCode,
            Files::KEY_MIME_TYPE      => $this->mimeType,
        ];
    }
}
