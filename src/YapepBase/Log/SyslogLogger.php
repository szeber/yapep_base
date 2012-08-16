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
use YapepBase\Log\Message\IMessage;
use YapepBase\Exception\ConfigException;

/**
 * SyslogLogger class
 *
 * Configuration:
 *     <ul>
 *         <li>applicationIdent: The name of the application as it will appear in the logs.</li>
 *         <li>facility: The facility to use for logging {@uses \YapepBase\SyslogLogger\SyslogLogger::LOG_*}</li>
 *         <li>includeSapiName: If TRUE, the SAPI's name will be appended to the applicationIdent. Optional.</li>
 *         <li>addPid: If TRUE, the current PID will be logged too. Optional.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Log
 */
class SyslogLogger extends LoggerAbstract {

	/**
	 * The syslog connection
	 * @var \YapepBase\Syslog\NativeSyslogConnection
	 */
	protected $connection;

	/**
	 * Creates a syslog connection.
	 *
	 * @param string                              $configName   The name of the configuration to use.
	 * @param \YapepBase\Syslog\ISyslogConnection $connection   The Syslog connection to use.
	 *
	 * @todo  2011-12-09  Janoszen  Move platform testing to a separate class.
	 */
	public function __construct($configName, \YapepBase\Syslog\ISyslogConnection $connection = null) {
		parent::__construct($configName);
		if ($connection) {
			$this->connection = $connection;
		//@codeCoverageIgnoreStart
		} elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$this->connection = new \YapepBase\Syslog\LegacySyslogConnection();
		} else {
			$this->connection = new \YapepBase\Syslog\NativeSyslogConnection();
		}
		//@codeCoverageIgnoreEnd
		$ident = $this->configOptions['applicationIdent'];
		if (isset($this->configOptions['includeSapiName']) && $this->configOptions['includeSapiName']) {
			$ident .= '-' . PHP_SAPI;
		}
		$this->connection->setIdent($ident);

		$this->connection->setFacility($this->configOptions['facility']);

		$options = 0;
		if (isset($this->configOptions['addPid']) && $this->configOptions['addPid']) {
			$options += \YapepBase\Syslog\ISyslogConnection::LOG_PID;
		}
		$this->connection->setOptions($options);
		$this->connection->open();
	}

	/**
	 * Closes the syslog connection.
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->connection->close();
	}

	/**
	 * Logs the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return void
	 */
	protected function logMessage(IMessage $message) {
		$this->connection->log($message->getPriority(), $this->getLogMessage($message));
	}

	/**
	 * Returns the log message prepared from the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return string
	 */
	protected function getLogMessage(IMessage $message) {
		$fields = $message->getFields();

		$logMessage = '[' . $message->getTag() . ']|';

		foreach ($fields as $key => $value) {
			$logMessage .= $key . '=' . $value . '|';
		}

		// We have to remove the line breaks, because syslog will create new log entry after every linebreak.
		$message = str_replace(PHP_EOL, '', $logMessage . 'tag=' . $message->getTag()
			. '|message=' . $message->getMessage());

		return $message;
	}

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
		parent::verifyConfig($configName);
		if (
			!is_array($this->configOptions) || empty($this->configOptions['facility'])
			|| empty($this->configOptions['applicationIdent'])
		) {
			throw new ConfigException('Configuration invalid for syslog: ' . $configName);
		}
	}

}