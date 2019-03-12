<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Storage;

use YapepBase\Application;
use YapepBase\Debugger\Item\StorageItem;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\File\Exception as FileException;
use YapepBase\Exception\File\NotFoundException;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use YapepBase\File\FileHandlerPhp;

/**
 * FileStorage class
 *
 * Configuration options:
 *     <ul>
 *         <li>path:             The full path to the directory to use. It must be writable.</li>
 *         <li>storePlainText:   If TRUE, the data will be stored as plain text, not serialized.
 *                               Disables TTL functionality. Optional, defaults to FALSE.</li>
 *         <li>filePrefix:       The files will be prefixed with this string.
 *                               No checking is done on the string. Optional, defaults to empty string.</li>
 *         <li>fileSuffix:       The files will be suffixed with this string.
 *                               No checking is done on the string. Optional, defaults to empty string.</li>
 *         <li>fileMode:         The mode of the files in unix octal notation. If path does not exists,
 *                               and will be created, this mode will be set for it. Optional, defaults to 0644.</li>
 *         <li>hashKey:          If TRUE, the key will be hashed before being used for the filename.
 *                               Optional, defaults to FALSE.</li>
 *         <li>readOnly:         If TRUE, the storage instance will be read only, and any write attempts will
 *                               throw an exception. Optional, defaults to FALSE</li>
 *         <li>debuggerDisabled: If TRUE, the storage will not add the requests to the debugger if it's available.
 *                               This is useful for example for a storage instance, that is used to store the
 *                               DebugDataCreator's debug information as they can become quite large, and if they were
 *                               sent to the client it can cause problems. Optional, defaults to FALSE.
 *     </ul>
 *
 * @todo locking
 */
class FileStorage extends StorageAbstract
{
    /**
     * The path to use for the file storage.
     *
     * @var string
     */
    protected $path;

    /**
     * TRUE if the backend is only capable of storing plain text data.
     *
     * @var bool
     */
    protected $storePlainText;

    /**
     * The prefix for the files.
     *
     * @var string
     */
    protected $filePrefix;

    /**
     * The suffix for the files.
     *
     * @var string
     */
    protected $fileSuffix;

    /**
     * The mode of the files.
     *
     * Octal number.
     *
     * @var int
     */
    protected $fileMode;

    /**
     * If TRUE, the keys will be hashed before being used as the filename.
     *
     * @var bool
     */
    protected $hashKey;

    /**
     * If TRUE, the storage will be read only.
     *
     * @var bool
     */
    protected $readOnly = false;

    /**
     * If TRUE, no debug items are created by this storage.
     *
     * @var bool
     */
    protected $debuggerDisabled;

    /**
     * The File handler object.
     *
     * @var \YapepBase\File\FileHandlerPhp
     */
    protected $fileHandler;

    /**
     * Constructor.
     *
     * @param string $configName   The name of the configuration to use.
     *
     * @throws \YapepBase\Exception\ConfigException    On configuration errors.
     * @throws \YapepBase\Exception\StorageException   On storage errors.
     */
    public function __construct($configName)
    {
        // This is needed to be able to mock the used file handler easily
        if (empty($this->fileHandler)) {
            $this->fileHandler = new FileHandlerPhp();
        }

        parent::__construct($configName);
    }

    /**
     * Returns the config properties(last part of the key) used by the class.
     *
     * @return array
     */
    protected function getConfigProperties()
    {
        return [
            'path',
            'storePlainText',
            'filePrefix',
            'fileSuffix',
            'fileMode',
            'hashKey',
            'readOnly',
            'debuggerDisabled',
        ];
    }

    /**
     * Sets up the backend.
     *
     * @param array $config   The configuration data for the backend.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ConfigException    On configuration errors.
     * @throws \YapepBase\Exception\StorageException   On storage errors.
     * @throws \YapepBase\Exception\File\Exception     On filesystem errors.
     */
    protected function setupConfig(array $config)
    {
        if (empty($config['path'])) {
            throw new ConfigException('Path is not set for FileStorage config ('
                . $this->currentConfigurationName . ')');
        }
        $this->path = $config['path'];
        if (!in_array(substr($this->path, -1, 1), ['/', '\\'])) {
            $this->path .= DIRECTORY_SEPARATOR;
        }

        $this->storePlainText   = empty($config['storePlainText']) ? false : (bool)$config['storePlainText'];
        $this->filePrefix       = empty($config['filePrefix']) ? '' : $config['filePrefix'];
        $this->fileSuffix       = empty($config['fileSuffix']) ? '' : $config['fileSuffix'];
        $this->fileMode         = empty($config['fileMode']) ? 0644 : $config['fileMode'];
        $this->hashKey          = empty($config['hashKey']) ? false : (bool)$config['hashKey'];
        $this->readOnly         = empty($config['readOnly']) ? false : (bool)$config['readOnly'];
        $this->debuggerDisabled = empty($config['debuggerDisabled']) ? false : (bool)$config['debuggerDisabled'];

        // If the given path does not exist
        if (!$this->fileHandler->checkIsPathExists($this->path)) {
            try {
                // Create the path
                $this->fileHandler->makeDirectory($this->path, ($this->fileMode | 0111), true);
            } catch (FileException $e) {
                throw new StorageException('Can not create directory for FileStorage: ' . $this->path, 0, $e);
            }
            // If the given path is not a directory
        } elseif (!$this->fileHandler->checkIsDirectory(rtrim($this->path, '/'))) {
            throw new StorageException('Path is not a directory for FileStorage: ' . $this->path);
        }

        // If this is not a readonly storage and a given path is not writable
        if (!$this->readOnly && !$this->fileHandler->checkIsWritable($this->path)) {
            throw new StorageException('Path is not writable for FileStorage: ' . $this->path);
        }
    }

    /**
     * Returns the full path for the specified filename
     *
     * @param string $fileName   The file name.
     *
     * @return string
     *
     * @throws StorageException   On invalid filename.
     */
    protected function makeFullPath($fileName)
    {
        $fileName = $this->filePrefix . $fileName . $this->fileSuffix;
        if ($this->hashKey) {
            $fileName = md5($fileName);
        }
        if (!preg_match('/^[-_.a-zA-Z0-9]+$/', $fileName)) {
            throw new StorageException('Invalid filename: ' . $fileName);
        }

        return $this->path . $fileName;
    }

    /**
     * Stores data the specified key
     *
     * @param string $key          The key to be used to store the data.
     * @param mixed  $data         The data to store.
     * @param int    $ttlInSeconds The expiration time of the data in seconds if supported by the backend.
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     * @throws \YapepBase\Exception\ParameterException    If TTL is set and not supported by the backend.
     *
     * @return void
     */
    public function set($key, $data, $ttlInSeconds = 0)
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }
        $startTime = microtime(true);
        $fileName  = $this->makeFullPath($key);

        try {
            $this->fileHandler->write($fileName, $this->prepareData($key, $data, $ttlInSeconds));
        } catch (FileException $e) {
            throw new StorageException('Unable to write data to FileStorage (file: ' . $fileName . ' )', 0, $e);
        }

        // Disable potential warnings if unit testing with vfsStream
        $this->fileHandler->changeMode($fileName, $this->fileMode);

        $debugger = Application::getInstance()->getDiContainer()->getDebugger();
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(new StorageItem(
                'file',
                'file.' . $this->currentConfigurationName,
                StorageItem::METHOD_SET . ' ' . $key . ' for ' . $ttlInSeconds,
                $data,
                microtime(true) - $startTime
            ));
        }
    }

    /**
     * Returns the data prepared to be written.
     *
     * @param string $key    The key.
     * @param mixed  $data   The data to save.
     * @param int    $ttl    The TTL.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ParameterException   If TTL is not supported by the backend.
     */
    protected function prepareData($key, $data, $ttl = 0)
    {
        if ($ttl != 0 && $this->storePlainText) {
            throw new ParameterException('TTL option is set for FileStorage with storePlainText config option.');
        }
        if ($this->storePlainText) {
            return (string)$data;
        }
        $time = time();

        // 0 TTL means the data should not expire.
        if (0 == $ttl) {
            $expiresAt = 0;
        } else {
            $expiresAt = $time + $ttl;
        }

        return serialize(['createdAt' => $time, 'expiresAt' => $expiresAt, 'data' => $data, 'key' => $key]);
    }

    /**
     * Processes the data read from the file
     *
     * @param string $data   The data.
     *
     * @return mixed
     *
     * @throws StorageException   On unserialization errors.
     */
    protected function readData($data)
    {
        if ($this->storePlainText) {
            return $data;
        }
        // Unserialization errors handled via exception
        $data = @unserialize($data);
        if (!is_array($data) || !isset($data['expiresAt']) || !isset($data['data'])) {
            throw new StorageException('Unable to unserialize stored data');
        }

        // If the expiresAt is empty, the data does not expire.
        if (!empty($data['expiresAt']) && $data['expiresAt'] < time()) {
            return false;
        }

        return $data['data'];
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @param string $key   The key.
     *
     * @return mixed
     *
     * @throws \YapepBase\Exception\StorageException   On error.
     */
    public function get($key)
    {
        $startTime = microtime(true);
        $fileName  = $this->makeFullPath($key);
        $data      = false;

        if ($this->fileHandler->checkIsPathExists($fileName)) {
            if (
                !$this->fileHandler->checkIsReadable($fileName)
                || ($contents = $this->fileHandler->getAsString($fileName)) === false
            ) {
                throw new StorageException('Unable to read file: ' . $fileName);
            }

            $data = $this->readData($contents);
            if (false === $data) {
                try {
                    $this->fileHandler->remove($fileName);
                } catch (FileException $e) {
                    throw new StorageException('Unable to remove empty file: ' . $fileName, 0, $e);
                }
            }
        }

        $debugger = Application::getInstance()->getDiContainer()->getDebugger();
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(new StorageItem(
                'file',
                'file.' . $this->currentConfigurationName,
                StorageItem::METHOD_GET . ' ' . $key,
                $data,
                microtime(true) - $startTime
            ));
        }

        return $data;
    }

    /**
     * Deletes the data specified by the key
     *
     * @param string $key   The ket.
     *
     * @throws \YapepBase\Exception\StorageException   If the Storage is read only.
     *
     * @return void
     */
    public function delete($key)
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }

        $startTime = microtime(true);
        $fileName  = $this->makeFullPath($key);

        try {
            $this->fileHandler->remove($fileName);
        } catch (NotFoundException $e) {
        } catch (FileException $e) {
            throw new StorageException('Unable to remove the file: ' . $fileName, 0, $e);
        }

        $debugger = Application::getInstance()->getDiContainer()->getDebugger();
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(new StorageItem(
                'file',
                'file.' . $this->currentConfigurationName,
                StorageItem::METHOD_DELETE . ' ' . $key,
                null,
                microtime(true) - $startTime
            ));
        }
    }

    /**
     * Deletes every data in the storage.
     *
     * @throws \YapepBase\Exception\StorageException   If the Storage is read only.
     *
     * @return void
     */
    public function clear()
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }

        $startTime = microtime(true);

        try {
            $this->fileHandler->removeDirectory($this->path, true);
        } catch (FileException $e) {
            throw new StorageException('Unable to remove the directory: ' . $this->path, 0, $e);
        }

        $debugger = Application::getInstance()->getDiContainer()->getDebugger();
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(new StorageItem(
                'file',
                'file.' . $this->currentConfigurationName,
                StorageItem::METHOD_CLEAR,
                null,
                microtime(true) - $startTime
            ));
        }
    }

    /**
     * Returns if the backend is persistent or volatile.
     *
     * If the backend is volatile a system or service restart may destroy all the stored data.
     *
     * @return bool
     */
    public function isPersistent()
    {
        // File storage is always persistent.
        return true;
    }

    /**
     * Returns whether the TTL functionality is supported by the backend.
     *
     * @return bool
     */
    public function isTtlSupported()
    {
        // If the storePlainText option is set to false, we support TTL functionality.
        return !$this->storePlainText;
    }

    /**
     * Returns TRUE if the storage backend is read only, FALSE otherwise.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Returns the configuration data for the storage backend. This includes the storage type as used by
     * the storage factory.
     *
     * @return array
     */
    public function getConfigData()
    {
        return [
            'storageType'      => StorageFactory::TYPE_FILE,
            'path'             => $this->path,
            'storePlainText'   => $this->storePlainText,
            'filePrefix'       => $this->filePrefix,
            'fileSuffix'       => $this->fileSuffix,
            'fileMode'         => $this->fileMode,
            'hashKey'          => $this->hashKey,
            'readOnly'         => $this->readOnly,
            'debuggerDisabled' => $this->debuggerDisabled,
        ];
    }
}
