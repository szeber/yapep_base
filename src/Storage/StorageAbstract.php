<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Storage;

use YapepBase\Config;
use YapepBase\Exception\ConfigException;

/**
 * Base class for the storage implementations.
 *
 * Configuration settings for the storage should be set in the format:
 * <b>resource.storage.&lt;configName&gt;.&lt;optionName&gt;
 */
abstract class StorageAbstract implements IStorage
{
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
    public function __construct($configName)
    {
        $this->currentConfigurationName = $configName;

        $properties = $this->getConfigProperties();
        $configData = [];
        foreach ($properties as $property) {
            try {
                $configData[$property] =
                    Config::getInstance()->get('resource.storage.' . $configName . '.' . $property);
            } catch (ConfigException $e) {
                // We just swallow this because we don't know what properties do we need in advance
            }
        }

        $this->setupConfig($configData);
    }

    /**
     * Returns the config properties(last part of the key) used by the class.
     *
     * @return array
     */
    abstract protected function getConfigProperties();

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
