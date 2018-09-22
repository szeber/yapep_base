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


use YapepBase\Debugger\Item\StorageItem;
use YapepBase\Exception\StorageException;
use YapepBase\Exception\ConfigException;
use YapepBase\Application;

/**
 * MemcachedStorage class
 *
 * Storage backend, that uses the memcached extension. For the memcache extension {@see MemcacheStorage}.
 * This is the preferred memcache implementation if the memcached extension is available on your system.
 *
 * Configuration options:
 *     <ul>
 *         <li>host:             The memcache server's hostname or IP.</li>
 *         <li>port:             The port of the memcache server. Optional, defaults to 11211</li>
 *         <li>keyPrefix:        The keys will be prefixed with this string. Optional, defaults to empty string.</li>
 *         <li>keySuffix:        The keys will be suffixed with this string. Optional, defaults to empty string.</li>
 *         <li>hashKey:          If TRUE, the key will be hashed before being stored. Optional, defaults to FALSE.</li>
 *         <li>readOnly:         If TRUE, the storage instance will be read only, and any write attempts will
 *                               throw an exception. Optional, defaults to FALSE</li>
 *         <li>debuggerDisabled: If TRUE, the storage will not add the requests to the debugger if it's available.
 *                               This is useful for example for a storage instance, that is used to store the
 *                               DebugDataCreator's debug information as they can become quite large, and if they were
 *                               sent to the client it can cause problems. Optional, defaults to FALSE.
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Storage
 * @todo locking
 */
class MemcachedStorage extends StorageAbstract implements IIncrementable {

	/**
	 * The memcache connection instance
	 *
	 * @var \Memcached
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
	 * If TRUE, no debug items are created by this storage.
	 *
	 * @var bool
	 */
	protected $debuggerDisabled;

	/**
	 * Returns the config properties(last part of the key) used by the class.
	 *
	 * @return array
	 */
	protected function getConfigProperties() {
		return array(
			'host',
			'port',
			'keyPrefix',
			'keySuffix',
			'hashKey',
			'readOnly',
			'debuggerDisabled'
		);
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
	 */
	protected function setupConfig(array $config) {
		if (empty($config['host'])) {
			throw new ConfigException('Host is not set for MemcachedStorage: ' . $this->currentConfigurationName);
		}
		$this->host             = $config['host'];
		$this->port             = empty($config['port'])             ? 11211 : (int)$config['port'];
		$this->keyPrefix        = empty($config['keyPrefix'])        ? ''    : $config['keyPrefix'];
		$this->keySuffix        = empty($config['keySuffix'])        ? ''    : $config['keySuffix'];
		$this->hashKey          = empty($config['hashKey'])          ? false : (bool)$config['hashKey'];
		$this->readOnly         = empty($config['readOnly'])         ? false : (bool)$config['readOnly'];
		$this->debuggerDisabled = empty($config['debuggerDisabled']) ? false : (bool)$config['debuggerDisabled'];

		$this->memcache = Application::getInstance()->getDiContainer()->getMemcached();
		$serverList = $this->memcache->getServerList();
		if (empty($serverList)) {
			$this->memcache->addServer($this->host, $this->port);
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

		$startTime = microtime(true);

		if (!$this->memcache->set($this->makeKey($key), $data, $ttl)) {
			$code = $this->memcache->getResultCode();
			if (\Memcached::RES_NOTSTORED !== $code) {
				throw new StorageException('Unable to store value in memcache. Error: '
					. $this->memcache->getResultMessage(), $this->memcache->getResultCode());
			}
		}

		// If we have a debugger, we have to log the request
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('memcached', 'memcached.' . $this->currentConfigurationName,
				StorageItem::METHOD_SET . ' ' . $key . ' for ' . $ttl, $data, microtime(true) - $startTime));
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

		$startTime = microtime(true);

		$result = $this->memcache->get($this->makeKey($key));
		if (false === $result) {
			$code = $this->memcache->getResultCode();
			if (\Memcached::RES_NOTFOUND !== $code && \Memcached::RES_SUCCESS !== $code) {
				throw new StorageException('Unable to get value in memcache. Error: '
					. $this->memcache->getResultMessage(), $this->memcache->getResultCode());
			}
		}
		// If we have a debugger, we have to log the request
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('memcached', 'memcached.' . $this->currentConfigurationName,
				StorageItem::METHOD_GET . ' ' . $key, $result, microtime(true) - $startTime));
		}

		return $result;
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

		$startTime = microtime(true);

		$this->memcache->delete($this->makeKey($key));

		// If we have a debugger, we have to log the request
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('memcached', 'memcached.' . $this->currentConfigurationName,
				StorageItem::METHOD_DELETE . ' ' . $key, null, microtime(true) - $startTime));
		}
	}

	/**
	 * Deletes every data in the storage.
	 *
	 * <b>Warning!</b> Flushes the whole memcached server
	 *
	 * @return mixed
	 */
	public function clear() {
		if ($this->readOnly) {
			throw new StorageException('Trying to write to a read only storage');
		}
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		$startTime = microtime(true);

		$this->memcache->flush();

		// If we have a debugger, we have to log the request
		if (!$this->debuggerDisabled && $debugger !== false) {
			$debugger->addItem(new StorageItem('memcached', 'memcached.' . $this->currentConfigurationName,
				StorageItem::METHOD_CLEAR, null, microtime(true) - $startTime));
		}
	}

	/**
	 * Increments (or decreases) the value of the key with the given offset.
	 *
	 * @param string $key      The key of the item to increment.
	 * @param int    $offset   The amount by which to increment the item's value.
	 *
	 * @return int   The changed value.
	 */
	public function increment($key, $offset, $ttl = 0) {
		return $this->memcache->increment($this->makeKey($key), $offset, 0, $ttl);
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

	/**
	 * Returns the configuration data for the storage backend. This includes the storage type as used by
	 * the storage factory.
	 *
	 * @return array
	 */
	public function getConfigData() {
		return array(
			'storageType'      => StorageFactory::TYPE_MEMCACHED,
			'host'             => $this->host,
			'port'             => $this->port,
			'keyPrefix'        => $this->keyPrefix,
			'keySuffix'        => $this->keySuffix,
			'hashKey'          => $this->hashKey,
			'readOnly'         => $this->readOnly,
			'debuggerDisabled' => $this->debuggerDisabled,
		);
	}
}
