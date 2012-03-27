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
use YapepBase\Exception\ConfigException;
use YapepBase\Config;

/**
 * StorageAbstract class
 *
 * @package    YapepBase
 * @subpackage Storage
 */
abstract class StorageAbstract implements IStorage {

	/**
	 * Constructor.
	 *
	 * @param string $configName   The name of the configuration to use.
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	public function __construct($configName) {
		$config = Config::getInstance()->get($configName, false);
		if (false === $config) {
			throw new ConfigException('Configuration not found: ' . $configName);
		}
		$this->setupConfig($config);
	}

	/**
	 * Sets up the backend.
	 *
	 * @param array $config   The configuration data for the backend.
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	abstract protected function setupConfig(array $config);
}