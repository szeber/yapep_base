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


use YapepBase\Config;
use YapepBase\Exception\StorageException;

/**
 * Factory class which returns the required Storage object by config.
 *
 * Configuration settings for the connections should be set in the format:
 * <b>resource.storage.&lt;connectionName&gt;.&lt;optionName&gt;</b>
 *
 * Generic configuration:
 *     <ul>
 *         <li>storageType: The storage type. {@uses self::TYPE_*}</li>
 *     </ul>
 *
 * @package YapepBase\Storage
 */
class StorageFactory {

	/** Dummy Storage type. */
	const TYPE_DUMMY = 'dummy';

	/** Memcached Storage type. */
	const TYPE_MEMCACHED = 'memcached';

	/** Memcache Storage type. */
	const TYPE_MEMCACHE = 'memcache';

	/** File Storage type. */
	const TYPE_FILE = 'file';

	/**
	 * Holds the storages.
	 *
	 * @var array
	 */
	protected static $storages = array();

	/**
	 * Returns the requested storage.
	 *
	 * @param string $configName   The name of the config.
	 *
	 * @throws \YapepBase\Exception\StorageException   If the given storage type is invalid.
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 *
	 * @return \YapepBase\Storage\StorageAbstract
	 */
	public static function get($configName) {
		if (!isset(static::$storages[$configName])) {
			$config = Config::getInstance();

			$storageType = $config->get('resource.storage.' . $configName . '.storageType');

			if (empty($storageType)) {
				throw new StorageException('No storageType configured in the config: ' . $configName);
			}

			static::$storages[$configName] = static::getStorage($configName, $storageType);

		}
		return static::$storages[$configName];
	}

	/**
	 * Returns a new Storage object.
	 *
	 * @param string $configName    The name of the config.
	 * @param string $storageType   The type of the storage to return {@uses self::TYPE_*}
	 *
	 * @throws \YapepBase\Exception\StorageException   If the given storage type is invalid.
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 *
	 * @return \YapepBase\Storage\StorageAbstract
	 */
	protected static function getStorage($configName, $storageType) {
		switch ($storageType) {
			case self::TYPE_DUMMY:
				return new DummyStorage($configName);
				break;

			case self::TYPE_MEMCACHED:
				return new MemcachedStorage($configName);
				break;

			case self::TYPE_MEMCACHE:
				return new MemcacheStorage($configName);
				break;

			case self::TYPE_FILE:
				return new FileStorage($configName);
				break;

			default:
				throw new StorageException('Invalid storageType given "'
					. $storageType . '" in the config: ' . $configName);
				break;
		}
	}
}