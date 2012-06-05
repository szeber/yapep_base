<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   BusinessObject
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\BusinessObject;

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Exception\ParameterException;
use YapepBase\Storage\IStorage;

/**
 * AbstractBo class which should be extended by every Bo classes.
 *
 * Must have global config options affecting this class:
 * <ul>
 *    <li>system.project.name: The unique name of the project. </li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage BusinessObject
 */
abstract class AbstractBo {

	/** Cache key prefix for storing keys. */
	const CACHE_KEY_FOR_KEYS_SUFFIX = 'bo.keys';

	/** TTL for storing keys in secs. (1 day in secs) */
	const CACHE_KEY_FOR_KEYS_TTL = 86400;

	/**
	 * Returns the prefix of the key which should be used for caching in the BO.
	 *
	 * @return string
	 */
	protected function getKeyPrefix() {
		return Config::getInstance()->get('system.project.name') . '.' . get_class($this) . '.';
	}

	/**
	 * Returns the key which should be used for storing the keys used by the BO.
	 *
	 * @return string
	 */
	private function getKeyForKeys() {
		return $this->getKeyPrefix() . self::CACHE_KEY_FOR_KEYS_SUFFIX;
	}

	/**
	 * Returns the storage handler which should be used for caching.
	 *
	 * @return bool|\YapepBase\Storage\IStorage
	 */
	protected function getStorage() {
		return Application::getInstance()->getDiContainer()->getMiddlewareStorage();
	}

	/**
	 * Adds the given key to the stored list.
	 *
	 * @param $key
	 */
	private function addKey($key) {
		$keys = $this->getStorage()->get($this->getKeyForKeys());
		$keys[] = $key;
		$keys = array_unique($keys);
		$this->getStorage()->set($this->getKeyForKeys(), $keys, self::CACHE_KEY_FOR_KEYS_TTL);
	}

	/**
	 * Returns the stored data for the given key.
	 *
	 * @param $key   The suffix of the key
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\ParameterException   On empty key.
	 * @throws \YapepBase\Exception\StorageException     On storage error.
	 */
	protected function getFromStorage($key) {
		if (empty($key)) {
			throw new ParameterException();
		}

		$key = $this->getKeyPrefix() . $key;

		return $this->getStorage()->get($key);
	}

	/**
	 * Stores data under the specified key
	 *
	 * @param string $key    The key to be used to store the data.
	 * @param mixed  $data   The data to store.
	 * @param int    $ttl    The expiration time of the data in seconds if supported by the backend.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   On empty key.
	 * @throws \YapepBase\Exception\StorageException     On error.
	 * @throws \YapepBase\Exception\ParameterException   If TTL is set and not supported by the backend.
	 */
	public function setToStorage($key, $data, $ttl = 0) {
		if (empty($key)) {
			throw new ParameterException();
		}

		$key = $this->getKeyPrefix() . $key;

		$this->getStorage()->set($key, $data, $ttl);

		$this->addKey($key);
	}

	/**
	 * Deletes the given keys from the storage.
	 *
	 * @param string $key   The name of the key to look for. The key can be the exact key,
	 *                      or can end in a '.*' for wildcard lookup. If its empty all the keys will be purged.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\StorageException   On error.
	 */
	protected function deleteFromStorage($key = '') {
		// Get the stored keys
		$keysStored = $this->getStorage()->get($this->getKeyForKeys());

		$keysToPurge = array();

		// If the given key is empty we have to purge everything
		if (empty($key)) {
			$keysToPurge = $keysStored;
			$keysStored = array();
		}
		// If it ends with an asterix, purge all the keys beginning with the name
		elseif ('.*' == substr($key, -2, 2)) {
			$keyPrefix = substr($key, 0, -1);

			foreach ($keysStored as $index => $storedKey) {
				// We've found a key with the given prefix
				if (0 === strpos($storedKey, $keyPrefix)) {
					$keysToPurge[] = $this->getKeyPrefix() . $storedKey;
					unset($keysStored[$index]);
				}
			}
		}
		// The given key is an exact key
		else {
			$keysToPurge[] = $this->getKeyPrefix() . $key;
			foreach ($keysStored as $index => $storedKey) {
				if ($storedKey == $key) {
					unset($keysStored[$index]);
					break;
				}
			}
		}

		// Removing the data from the found keys
		foreach ($keysToPurge as $keyToPurge) {

			$this->getStorage()->delete($keyToPurge);
		}
		// Writing back the remaining keys
		$this->getStorage()->set($this->getKeyForKeys(), $keysStored, self::CACHE_KEY_FOR_KEYS_TTL);
	}
}