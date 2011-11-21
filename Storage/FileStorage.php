<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Storage
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Storage;

use YapepBase\Exception\ParameterException;

use YapepBase\Exception\StorageException;
use YapepBase\Exception\ConfigException;
use YapepBase\Config;

/**
 * FileStorage class
 *
 * Configuration options:
 *     <ul>
 *         <li>path:           The full path to the directory to use. It must be writable.</li>
 *         <li>storePlainText: If TRUE, the data will be stored as plain text, not serialized.
 *                             Disables TTL functionality. Optional, defaults to FALSE.</li>
 *         <li>filePrefix:     The files will be prefixed with this string.
 *                             No checking is done on the string. Optional, defaults to empty string.</li>
 *         <li>fileSuffix:     The files will be suffixed with this string.
 *                             No checking is done on the string. Optional, defaults to empty string.</li>
 *         <li>fileMode:       The mode of the files in unix octal notation. If path does not exists,
 *                             and will be created, this mode will be set for it. Optional, defaults to 0755.</li>
 *         <li>hashKey:        If TRUE, the key will be hashed before being used for the filename.
 *                             Optional, defaults to FALSE.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Storage
 */
class FileStorage extends StorageAbstract {

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
     * Sets up the backend.
     *
     * @param array $config   The configuration data for the backend.
     *
     * @throws \YapepBase\Exception\ConfigException    On configuration errors.
     * @throws \YapepBase\Exception\StorageException   On storage errors.
     */
    protected function setupConfig(array $config) {
        if (empty($config['path'])) {
            throw new ConfigException('Path is not set for FileStorage config');
        }
        $this->path = $config['path'];
        if (!in_array(substr($this->path, -1, 1), array('/', '\\'))) {
            $this->path .= DIRECTORY_SEPARATOR;
        }
        $this->storePlainText = (isset($config['storePlainText']) && $config['storePlainText']);
        $this->filePrefix = (isset($config['filePrefix']) ? $config['filePrefix'] : '');
        $this->fileSuffix = (isset($config['fileSuffix']) ? $config['filePrefix'] : '');
        $this->fileMode = (empty($config['fileMode']) ? 0755 : $config['fileMode']);
        $this->hashKey = (isset($config['hashKey']) ? (bool)$config['hashKey'] : false);

        if (!file_exists($this->path)) {
            if (!mkdir($this->path, $this->fileMode, true)) {
                throw new StorageException('Can not create directory for FileStorage');
            }
        } elseif (!is_dir($this->path)) {
            throw new StorageException('Path is not a directory for FileStorage');
        }

        if (!is_writable($this->path)) {
            throw new StorageException('Path is not writable for FileStorage');
        }
    }

    /**
     * Returns the full path for the specified filename
     *
     * @param string $fileName
     *
     * @return string
     *
     * @throws StorageException   On invalid filename.
     */
    protected function makeFullPath($fileName) {
        $fileName = $this->filePrefix . $fileName  . $this->fileSuffix;
        if ($this->hashKey) {
            $fileName = md5($fileName);
        }
        if (!preg_match('^[-_.a-zA-Z0-9]+$')) {
            throw new StorageException('Invalid filename');
        }
        return $this->path . $fileName;
    }

    /**
     * Stores data the specified key
     *
     * @param string $key    The key to be used to store the data.
     * @param mixed  $data   The data to store.
     * @param int    $ttl    The expiration time of the data in seconds if supported by the backend.
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     * @throws \YapepBase\Exception\ParameterException    If TTL is set and not supported by the backend.
     */
    public function set($key, $data, $ttl = 0) {
        $fileName = $this->makeFullPath($key);
        if (false === file_put_contents($fileName, $this->prepareData($data, $ttl))) {
            throw new StorageException('Unable to write data to FileStorage');
        }
        chmod($fileName, $this->fileMode);
    }

    /**
     * Returns the data prepared to be written.
     *
     * @param string $key
     * @param mixed  $data
     * @param int    $ttl
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ParameterException   If TTL is not suppored by the backend.
     */
    protected function prepareData($key, $data, $ttl = 0) {
        if ($ttl != 0 && $this->storePlainText) {
            throw new ParameterException('TTL option is set for FileSorage with storePlainText config option.');
        }
        if ($this->storePlainText) {
            return (string)$data;
        }
        $time = time();
        return serialize(array('createdAt' => $time, 'expiresAt' => $time + $ttl, 'data' => $data, 'key' => $key));
    }

    /**
     * Processes the data read from the file
     *
     * @param string $data
     *
     * @return mixed
     *
     * @throws StorageException   On unserialization errors.
     */
    protected function readData($data) {
        if ($this->storePlainText) {
            return $data;
        }
        $data = unserialize($data);
        if (!is_array($data) || !isset($data['expiresAt']) || !isset($data['data'])) {
            throw new StorageException('Unable to unserialize stored data');
        }
        if ($data['expiresAt'] < time()) {
            return false;
        }
        return $data['data'];
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     */
    public function get($key) {
        $fileName = $this->makeFullPath($key);
        if (file_exists($fileName)) {
            if (!is_readable($fileName) || false === ($contents = file_get_contents($fileName))) {
                throw new StorageException('Unable to read file');
            }
            return $contents;
        }
        return false;
    }

    /**
     * Deletes the data specified by the key
     *
     * @param string $key
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     */
    public function delete($key) {
        $fileName = $this->makeFullPath($key);
        if (file_exists($fileName)) {
            if (!unlink($fileName)) {
                throw new StorageException('Unable to delete file');
            }
        }
    }

    /**
     * Returns if the backend is persistent or volatile.
     *
     * If the backend is volatile a system or service restart may destroy all the stored data.
     *
     * @return bool
     */
    public function isPersistent() {
        // File storage is always persistent.
        return true;
    }

    /**
     * Returns whether the TTL functionality is supported by the backend.
     *
     * @return bool
     */
    public function isTtlSupported() {
        // If the storePlainText option is set to false, we support TTL functionality.
        return !$this->storePlainText;
    }
}