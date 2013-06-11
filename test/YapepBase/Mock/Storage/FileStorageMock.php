<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\Storage
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Storage;


use YapepBase\Storage\FileStorage;

/**
 * Mock class for FileStorage.
 *
 * @package    YapepBase
 * @subpackage Mock\Storage
 */
class FileStorageMock extends FileStorage {

	/**
	 * Constructor.
	 *
	 * @param string $configName   The name of the configuration to use.
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	public function __construct($configName, \YapepBase\File\IFileHandler $fileHandler) {
		$this->fileHandler = $fileHandler;
		parent::__construct($configName);
	}

	/**
	 * Returns the full path for the specified filename
	 *
	 * @param string $fileName   The file name.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\StorageException   On invalid filename.
	 */
	public function makeFullPath($fileName) {
		return parent::makeFullPath($fileName);
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
	public function prepareData($key, $data, $ttl = 0) {
		return parent::prepareData($key, $data, $ttl);
	}

	/**
	 * Processes the data read from the file
	 *
	 * @param string $data   The data.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\StorageException   On unserialization errors.
	 */
	public function readData($data) {
		return parent::readData($data);
	}

	/**
	 * Returns the path.
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Returns the storePlainText setting.
	 *
	 * @return string
	 */
	public function getStorePlainText() {
		return $this->storePlainText;
	}

	/**
	 * Returns the readOnly setting.
	 *
	 * @return bool
	 */
	public function getReadOnly() {
		return $this->readOnly;
	}

	/**
	 * Returns the hashKey setting.
	 *
	 * @return bool
	 */
	public function getHashKey() {
		return $this->hashKey;
	}

	/**
	 * Returns the filePrefix setting.
	 *
	 * @return string
	 */
	public function getFilePrefix() {
		return $this->filePrefix;
	}

	/**
	 * Returns the fileSuffix setting.
	 *
	 * @return string
	 */
	public function getFileSuffix() {
		return $this->fileSuffix;
	}

	/**
	 * Returns the fileMode setting.
	 *
	 * @return int
	 */
	public function getFileMode() {
		return $this->fileMode;
	}

	/**
	 * Returns the debuggerDisabled setting.
	 *
	 * @return bool
	 */
	public function getDebuggerDisabled() {
		return $this->debuggerDisabled;
	}

}
