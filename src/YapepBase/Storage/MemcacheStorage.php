<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Storage
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Storage;
use YapepBase\Application;
use YapepBase\Exception\StorageException;
use YapepBase\Exception\ConfigException;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Debugger\IDebugger;

/**
 * MemcacheStorage class
 *
 * Storage backend, that uses the memcache extension. For the memcached extension {@see MemcachedStorage}.
 * Use the MemcachedStorage class that uses the newer, more complete memcached extension if it is available on your
 * system.
 *
 * Configuration options:
 *     <ul>
 *         <li>host:        The memcache server's hostname or IP.</li>
 *         <li>port:        The port of the memcache server. Optional, defaults to 11211</li>
 *         <li>keyPrefix:   The keys will be prefixed with this string. Optional, defaults to empty string.</li>
 *         <li>keySuffix:   The keys will be suffixed with this string. Optional, defaults to empty string.</li>
 *         <li>hashKey:     If TRUE, the key will be hashed before being stored. Optional, defaults to FALSE.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Storage
 * @todo locking
 */
class MemcacheStorage extends StorageAbstract {

	/**
	 * The memcache connection instance
	 *
	 * @var \Memcache
	 */
	protected $memcache;

	/**
	 * The memcache host
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * The memcache port
	 *
	 * @var int
	 */
	protected $port;

	/**
	 * The string to prefix the keys with
	 *
	 * @var string
	 */
	protected $keyPrefix;

	/**
	 * The string to suffix the keys with
	 *
	 * @var string
	 */
	protected $keySuffix;

	/**
	 * If TRUE, the key will be hashed before storing
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
	 * Sets up the backend.
	 *
	 * @param array $config   The configuration data for the backend.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	protected function setupConfig(array $config) {
		if (empty($config['host'])) {
			throw new ConfigException('Host is not set for MemcacheStorage: ' . $this->currentConfigurationName);
		}
		$this->host = $config['host'];
		$this->port = (isset($config['port']) ? (int)$config['port'] : 11211);
		$this->keyPrefix = (isset($config['keyPrefix']) ? $config['keyPrefix'] : '');
		$this->keySuffix = (isset($config['keySuffix']) ? $config['keySuffix'] : '');
		$this->hashKey = (isset($config['hashKey']) ? (bool)$config['hashKey'] : false);
		$this->readOnly = (isset($config['readOnly']) ? (bool)$config['readOnly'] : false);

		$this->memcache = Application::getInstance()->getDiContainer()->getMemcache();
		if (!$this->memcache->connect($this->host, $this->port)) {
			throw new StorageException('MemcacheStorage is unable to connect to server: '
				. $this->host . ':' . $this->port);
		}
	}

	/**
	 * Returns the key ready to be used on the backend.
	 *
	 * @param string $key   The base key.
	 *
	 * @return string
	 */
	protected function makeKey($key) {
		$key = $this->keyPrefix . $key . $this->keySuffix;
		if ($this->hashKey) {
			$key = md5($key);
		}
		return $key;
	}

	/**
	 * Stores data the specified key
	 *
	 * @param string $key    The key to be used to store the data.
	 * @param mixed  $data   The data to store.
	 * @param int    $ttl    The expiration time of the data in seconds if supported by the backend.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\StorageException      On error.
	 * @throws \YapepBase\Exception\ParameterException    If TTL is set and not supported by the backend.
	 */
	public function set($key, $data, $ttl = 0) {
		if ($this->readOnly) {
			throw new StorageException('Trying to write to a read only storage');
		}
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'memcache.' . $this->currentConfigurationName,
				'set ' . $key . ' for ' . $ttl, $data);
			$startTime = microtime(true);
		}

		$this->memcache->set($this->makeKey($key), $data, 0, $ttl);

		// If we have a debugger, we have to log the execution time
		if ($debugger !== false) {
			$debugger->logQueryExecutionTime(IDebugger::QUERY_TYPE_CACHE, $queryId, microtime(true) - $startTime);
		}
	}

	/**
	 * Retrieves data from the cache identified by the specified key
	 *
	 * @param string $key   The key.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\StorageException      On error.
	 */
	public function get($key) {
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'memcache.' . $this->currentConfigurationName,
				'get ' . $key);
			$startTime = microtime(true);
		}

		$value = $this->memcache->get($this->makeKey($key));

		// If we have a debugger, we have to log the execution time
		if ($debugger !== false) {
			$debugger->logQueryExecutionTime(IDebugger::QUERY_TYPE_CACHE, $queryId, microtime(true) - $startTime,
				$value);
		}

		return $value;
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
		if ($this->readOnly) {
			throw new StorageException('Trying to write to a read only storage');
		}
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		// If we have a debugger, we have to log the query
		if ($debugger !== false) {
			$queryId = $debugger->logQuery(IDebugger::QUERY_TYPE_CACHE, 'memcache.' . $this->currentConfigurationName,
				'delete ' . $key);
			$startTime = microtime(true);
		}

		$this->memcache->delete($this->makeKey($key));

		// If we have a debugger, we have to log the execution time
		if ($debugger !== false) {
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
		// Memcache is cleared on restart of the memcache service, so it's never considered persistent.
		return false;
	}

	/**
	 * Returns whether the TTL functionality is supported by the backend.
	 *
	 * @return bool
	 */
	public function isTtlSupported() {
		// Memcache has TTL support
		return true;
	}

	/**
	 * Returns TRUE if the storage backend is read only, FALSE otherwise.
	 *
	 * @return bool
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}

}