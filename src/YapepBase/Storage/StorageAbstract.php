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
use YapepBase\Exception\ConfigException;
use YapepBase\Config;

/**
 * Base class for the storage implementations.
 *
 * Configuration settings for the storage should be set in the format:
 * <b>resource.storage.&lt;configName&gt;.&lt;optionName&gt;
 *
 * @package    YapepBase
 * @subpackage Storage
 */
abstract class StorageAbstract implements IStorage {

	/**
	 * Holds the name of the currently used configuration.
	 *
	 * @var string
	 */
	protected $currentConfigurationName;

	/**
	 * Constructor.
	 *
	 * @param string $configName   The name of the configuration to use.
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	public function __construct($configName) {
		$this->currentConfigurationName = $configName;

		$config = Config::getInstance()->get('resource.storage.' . $configName . '.*', false);
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
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException    On configuration errors.
	 * @throws \YapepBase\Exception\StorageException   On storage errors.
	 */
	abstract protected function setupConfig(array $config);
}