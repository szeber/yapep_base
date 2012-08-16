<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Log
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Log;
use YapepBase\Exception\ConfigException;
use YapepBase\Config;
use YapepBase\Log\Message\IMessage;

/**
 * Abstract base class usable by loggers.
 *
 * Configuration settings for the loggers should be set in the format:
 * <b>resource.log.&lt;configName&gt;.&lt;optionName&gt;
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

		$result = $config->get('resource.log.' . $configName . '.*', false);
		if (false === $result) {
			throw new ConfigException('Configuration not found for log resource: ' . $configName);
		}
		$this->configOptions = $result;
		$this->verifyConfig($configName);
	}

	/**
	 * It really logs the message.
	 *
	 * @param Message\IMessage $message   The message that should be logged.
	 *
	 * @return void
	 */
	abstract protected function logMessage(IMessage $message);

	/**
	 * Verifies the configuration. If there is an error with the config, it throws an exception.
	 *
	 * @param string $configName   The name of the configuration to validate.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException   On configuration errors.
	 */
	protected function verifyConfig($configName) {
		// Default implementation does nothing, override in descendant classes to validate the configuration
	}

	/**
	 * Logs the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return void
	 */
	public function log(IMessage $message) {
		if (!$message->checkIsEmpty()) {
			$this->logMessage($message);
		}
	}
}