<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Storage
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Storage;


use YapepBase\Application;
use YapepBase\Debugger\Item\StorageItem;

/**
 * Storage class for dummy storage.
 *
 * It wont store anything, and it will return false for every data query attempt.
 *
 * Configuration options:
 *     <ul>
 *         <li>debuggerDisabled: If TRUE, the storage will not add the requests to the debugger if it's available.
 *                               This is useful for example for a storage instance, that is used to store the
 *                               DebugDataCreator's debug information as they can become quite large, and if they were
 *                               sent to the client it can cause problems. Optional, defaults to FALSE.
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Storage
 */
class DummyStorage extends StorageAbstract {

	/**
	 * If TRUE, no debug items are created by this storage.
	 *
	 * @var bool
	 */
	protected $debuggerDisabled;

	/**
	 * Stores data the specified key
	 *
	 * @param string $key          The key to be used to store the data.
	 * @param mixed  $data         The data to store.
	 * @param int    $ttlInSeconds The expiration time of the data in seconds if supported by the backend.
	 *
	 * @return void
	 */
	public function set($key, $data, $ttlInSeconds = 0) {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('dummy', 'dummy.' . $this->currentConfigurationName,
				StorageItem::METHOD_SET . ' ' . $key . ' for ' . $ttlInSeconds, $data, 0));
		}
	}

	/**
	 * Retrieves data from the cache identified by the specified key. Returns FALSE if the key does not exist.
	 *
	 * @param string $key   The key.
	 *
	 * @return mixed   The data or FALSE if the specified key does not exist.
	 */
	public function get($key) {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('dummy', 'dummy.' . $this->currentConfigurationName,
				StorageItem::METHOD_GET . ' ' . $key, false, 0));
		}

		return false;
	}

	/**
	 * Deletes the data specified by the key
	 *
	 * @param string $key   The key.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\StorageException      On error.
	 */
	public function delete($key) {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('dummy', 'dummy.' . $this->currentConfigurationName,
				StorageItem::METHOD_DELETE . ' ' . $key, null, 0));
		}
	}

	/**
	 * Deletes every data in the storage.
	 *
	 * @return mixed
	 */
	public function clear() {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('dummy', 'dummy.' . $this->currentConfigurationName,
				StorageItem::METHOD_CLEAR, null, 0));
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
		return false;
	}

	/**
	 * Returns whether the TTL functionality is supported by the backend.
	 *
	 * @return bool
	 */
	public function isTtlSupported() {
		return false;
	}

	/**
	 * Returns TRUE if the storage backend is read only, FALSE otherwise.
	 *
	 * @return bool
	 */
	public function isReadOnly() {
		return false;
	}

	/**
	 * Returns the config properties(last part of the key) used by the class.
	 *
	 * @return array
	 */
	protected function getConfigProperties() {
		return array('debuggerDisabled');
	}

	/**
	 * Sets up the backend.
	 *
	 * @param array $config   The configuration data for the backend.
	 *
	 * @return void
	 */
	protected function setupConfig(array $config) {
		$this->debuggerDisabled = isset($config['debuggerDisabled']) ? (bool)$config['debuggerDisabled'] : false;
	}

	/**
	 * Returns the configuration data for the storage backend. This includes the storage type as used by
	 * the storage factory.
	 *
	 * @return array
	 */
	public function getConfigData() {
		return array(
			'storageType'      => StorageFactory::TYPE_DUMMY,
			'debuggerDisabled' => $this->debuggerDisabled,
		);
	}
}
