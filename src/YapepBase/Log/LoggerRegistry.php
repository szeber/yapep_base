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


use YapepBase\Exception\Log\LoggerNotFoundException;
use YapepBase\Log\Message\IMessage;

/**
 * Registry class storing the registered loggers.
 *
 * @package    YapepBase
 * @subpackage Log
 */
class LoggerRegistry implements ILogger {

	/**
	 * Registered loggers.
	 *
	 * @var array
	 */
	protected $loggers = array();

	/**
	 * Adds an error handler to the registry.
	 *
	 * @param \YapepBase\Log\ILogger $logger   The logger to add.
	 *
	 * @return void
	 */
	public function addLogger(ILogger $logger) {
		$this->loggers[] = $logger;
	}

	/**
	 * Removes a logger from the registry.
	 *
	 * @param \YapepBase\Log\ILogger $logger   The logger to remove.
	 *
	 * @return bool   TRUE if the logger was removed successfully, FALSE otherwise.
	 */
	public function removeLogger(ILogger $logger) {
		$index = array_search($logger, $this->loggers);
		if (false === $index) {
			return false;
		}
		unset($this->loggers[$index]);
		return true;
	}

	/**
	 * Returns the loggers assigned to the registry.
	 *
	 * @return array
	 */
	public function getLoggers() {
		return $this->loggers;
	}

	/**
	 * Logs the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Log\LoggerNotFoundException   If there are no registered Loggers in the registry.
	 */
	public function log(IMessage $message) {
		if (!$message->checkIsEmpty()) {

			if (empty($this->loggers)) {
				throw new LoggerNotFoundException('There are no registered Loggers!');
			}

			foreach ($this->loggers as $logger) {
				/** @var ILogger $logger */
				$logger->log($message);
			}
		}
	}
}