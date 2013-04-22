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
use YapepBase\Debugger\IDebugger;

/**
 * Storage class for dummy storage.
 *
 * It wont store anything, and it will return false for every data query attempt.
 *
 * @package    YapepBase
 * @subpackage Storage
 */
class DummyStorage extends StorageAbstract {

	/**
	 * Stores data the specified key
	 *
	 * @param string $key    The key to be used to store the data.
	 * @param mixed  $data   The data to store.
	 * @param int    $ttl    The expiration time of the data in seconds if supported by the backend.
	 *
	 * @return void
	 */
	public function set($key, $data, $ttl = 0) {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'dummy.' . $this->currentConfigurationName,
				'set ' . $key . ' for ' . $ttl, $data);
			$startTime = microtime(true);
			$debugger->logQueryExecutionTime(IDebugger::QUERY_TYPE_CACHE, $queryId, microtime(true) - $startTime);
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
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'dummy.' . $this->currentConfigurationName,
				'get ' . $key);
			$startTime = microtime(true);
			$debugger->logQueryExecutionTime(IDebugger::QUERY_TYPE_CACHE, $queryId, microtime(true) - $startTime,
				false);
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
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'dummy.' . $this->currentConfigurationName,
				'delete ' . $key);
			$startTime = microtime(true);

			$debugger->logQueryExecutionTime(IDebugger::QUERY_TYPE_CACHE, $queryId, microtime(true) - $startTime);
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
	 * Sets up the backend.
	 *
	 * @param array $config   The configuration data for the backend.
	 *
	 * @return void
	 */
	protected function setupConfig(array $config) {
	}
}