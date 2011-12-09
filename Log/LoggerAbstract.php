<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Log
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Log;
use YapepBase\Exception\ConfigException;
use YapepBase\Config;

/**
 * LoggerAbstract class
 *
 * @package    YapepBase
 * @subpackage Log
 */
abstract class LoggerAbstract implements ILogger {

    /**
     * Stores the configuration options
     *
     * @var array
     */
    protected $configOptions;

    /**
     * Constructor. Sets and validates the config for the logger.
     *
     * @param string $configName   The name of the configuration to use for the logger.
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration errors.
     */
    public function __construct($configName) {
        $config = Config::getInstance();
        $result = $config->get($configName, false);
        if (false === $result) {
            // Config not found by name, try with a wildcard query
            $result = $config->get($configName . '.*');
            //@todo  2011-12-09  janoszen  $result will never be false, except if the value is false.
            if (false === $result) {
                throw new ConfigException('Configuration not found: ' . $configName);
            }
        }
        $this->configOptions = $result;
        $this->verifyConfig($configName);
    }

    /**
     * Verifies the configuration. If there is an error with the config, it throws an exception.
     *
     * @param string $configName   The name of the configuration to validate.
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration errors.
     */
    protected function verifyConfig($configName) {
        // Default implementation does nothing, override in descendant classes to validate the configuration
    }
}