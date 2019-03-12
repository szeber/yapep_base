<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\File\Exception as FileException;
use YapepBase\Exception\File\NotFoundException;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use YapepBase\File\FileHandlerPhp;
use YapepBase\File\IFileHandler;
use YapepBase\Helper\DateHelper;

/**
 * Storage what's based on the filesystem.
 *
 * Creates files under the given path.
 */
class FileStorage extends StorageAbstract
{
    /** @var string */
    protected $path;

    /** @var bool */
    protected $canOnlyStorePlainText = false;

    /** @var string */
    protected $filenamePrefix = '';

    /** @var string */
    protected $filenameSuffix = '';

    /** @var int */
    protected $fileModeOctal = 0644;

    /** @var bool */
    protected $hashKey = false;

    /** @var bool */
    protected $readOnly;

    /** @var FileHandlerPhp */
    protected $fileHandler;

    /** @var DateHelper */
    protected $dateHelper;

    /**
     * @throws ParameterException
     * @throws StorageException
     */
    public function __construct(IFileHandler $fileHandler, DateHelper $dateHelper, string $path, bool $readOnly = false)
    {
        $this->fileHandler = $fileHandler;
        $this->dateHelper  = $dateHelper;
        $this->readOnly    = $readOnly;

        $this->setPath($path);
    }

    /**
     * @throws ParameterException
     * @throws StorageException
     */
    public function setPath(string $path)
    {
        if (empty($path)) {
            throw new ParameterException('Path has to be set');
        }

        if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->path = $path;

        $this->initAndValidatePath();
    }

    /**
     * @throws StorageException
     */
    protected function initAndValidatePath()
    {
        if (!$this->fileHandler->checkIsPathExists($this->path)) {
            try {
                $this->fileHandler->makeDirectory($this->path, ($this->fileModeOctal | 0111), true);
            } catch (FileException $e) {
                throw new StorageException('Can not create directory for FileStorage: ' . $this->path, 0, $e);
            }
        } elseif (!$this->fileHandler->checkIsDirectory(rtrim($this->path, '/'))) {
            throw new StorageException('Path is not a directory for FileStorage: ' . $this->path);
        }

        if (!$this->readOnly && !$this->fileHandler->checkIsWritable($this->path)) {
            throw new StorageException('Path is not writable for FileStorage: ' . $this->path);
        }
    }

    /**
     * Returns the full path for the specified filename
     *
     * @throws StorageException
     */
    protected function getFullPath(string $fileName): string
    {
        $fileName = $this->filenamePrefix . $fileName . $this->filenameSuffix;
        if ($this->hashKey) {
            $fileName = md5($fileName);
        }

        if (!preg_match('/^[-_.a-zA-Z0-9]+$/', $fileName)) {
            throw new StorageException('Invalid filename: ' . $fileName);
        }

        return $this->path . $fileName;
    }

    public function set(string $key, $data, int $ttlInSeconds = 0): void
    {
        $this->protectWhenReadOnly();

        $fullPath = $this->getFullPath($key);

        try {
            $item = (new Storage(Storage::METHOD_SET, $key, $data));

            $this->fileHandler->write($fullPath, $this->prepareDataToStore($data, $ttlInSeconds));

            $item->setFinished();
            $this->getDebugDataHandlerRegistry()->addStorage($item);
        } catch (FileException $e) {
            throw new StorageException('Unable to write data to FileStorage (file: ' . $fullPath . ' )', 0, $e);
        }

        // Disable potential warnings if unit testing with vfsStream
        $this->fileHandler->changeMode($fullPath, $this->fileModeOctal);
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @throws StorageException
     */
    public function get(string $key)
    {
        $item = (new Storage(Storage::METHOD_GET, $key));

        $fileName = $this->getFullPath($key);
        $data     = null;

        if ($this->fileHandler->checkIsPathExists($fileName)) {
            if (
                !$this->fileHandler->checkIsReadable($fileName)
                || ($contents = $this->fileHandler->getAsString($fileName)) === false
            ) {
                throw new StorageException('Unable to read file: ' . $fileName);
            }

            $file = $this->readData($contents);

            if ($this->isExpired($file)) {
                try {
                    $this->fileHandler->remove($fileName);
                } catch (FileException $e) {
                    throw new StorageException('Unable to remove empty file: ' . $fileName, 0, $e);
                }
            } else {
                $data = $file->data;
            }
        } else {
            $item->setData($data)->setFinished();
        }
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        return $data;
    }

    /**
     * Returns the data prepared to be written.
     *
     * @throws ParameterException
     */
    protected function prepareDataToStore($data, int $ttlInSeconds = 0): string
    {
        if ($ttlInSeconds != 0 && $this->canOnlyStorePlainText) {
            throw new ParameterException('TTL can not be used with plain text mode!');
        }

        if ($this->canOnlyStorePlainText) {
            return (string)$data;
        }

        $createdAt = $this->dateHelper->getCurrentTimestamp();
        $expiresAt = 0;

        if ($ttlInSeconds > 0) {
            $expiresAt = $createdAt + $ttlInSeconds;
        }

        $file = new File($data, $createdAt, $expiresAt);

        return json_encode($file);
    }

    /**
     * Processes the data read from the file
     *
     * @throws StorageException
     */
    protected function readData(string $fileContent): File
    {
        if ($this->canOnlyStorePlainText) {
            return new File($fileContent);
        }

        /** @var File $fileContent */
        $encodedFileContent = json_decode($fileContent);

        if (!property_exists($encodedFileContent, 'data') || !property_exists($encodedFileContent, 'expiresAt')) {
            throw new StorageException('Unable to retrieve stored data');
        }

        return $encodedFileContent;
    }

    protected function isExpired(File $file): bool
    {
        $currentTime = $this->dateHelper->getCurrentTimestamp();

        if (!empty($file->expiresAt) && $file->expiresAt < $currentTime) {
            return true;
        }

        return false;
    }

    /**
     * Deletes the data specified by the key
     *
     * @throws StorageException
     */
    public function delete(string $key): void
    {
        $item = (new Storage(Storage::METHOD_DELETE, $key));

        $this->protectWhenReadOnly();

        $fileName = $this->getFullPath($key);

        try {
            $this->fileHandler->remove($fileName);
        } catch (NotFoundException $e) {
        } catch (FileException $e) {
            throw new StorageException('Unable to remove the file: ' . $fileName, 0, $e);
        }

        $item->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    /**
     * Deletes every data in the storage.
     *
     * @throws StorageException
     */
    public function clear(): void
    {
        $item = (new Storage(Storage::METHOD_CLEAR));

        $this->protectWhenReadOnly();

        try {
            $this->fileHandler->removeDirectory($this->path, true);
        } catch (FileException $e) {
            throw new StorageException('Unable to remove the directory: ' . $this->path, 0, $e);
        }

        $item->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    public function isPersistent(): bool
    {
        // File storage is always persistent.
        return true;
    }

    public function isTtlSupported(): bool
    {
        // If the storePlainText option is set to false, we support TTL functionality.
        return !$this->canOnlyStorePlainText;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function canOnlyStorePlainText(): bool
    {
        return $this->canOnlyStorePlainText;
    }

    public function setCanOnlyStorePlainText(bool $canOnlyStorePlainText): self
    {
        $this->canOnlyStorePlainText = $canOnlyStorePlainText;

        return $this;
    }

    public function getFilenamePrefix(): string
    {
        return $this->filenamePrefix;
    }

    public function setFilenamePrefix(string $filenamePrefix): self
    {
        $this->filenamePrefix = $filenamePrefix;

        return $this;
    }

    public function getFilenameSuffix(): string
    {
        return $this->filenameSuffix;
    }

    public function setFilenameSuffix(string $filenameSuffix): self
    {
        $this->filenameSuffix = $filenameSuffix;

        return $this;
    }

    public function getFileModeOctal(): int
    {
        return $this->fileModeOctal;
    }

    public function setFileModeOctal(int $fileModeOctal): self
    {
        $this->fileModeOctal = $fileModeOctal;

        return $this;
    }

    public function isKeyHashed(): bool
    {
        return $this->hashKey;
    }

    public function setHashKey(bool $hashKey): self
    {
        $this->hashKey = $hashKey;

        return $this;
    }

    public function getFileHandler(): FileHandlerPhp
    {
        return $this->fileHandler;
    }
}
