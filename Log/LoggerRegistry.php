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
	 * @param \YapepBase\Log\ILogger $logger
	 */
	public function addLogger(ILogger $logger) {
		$this->loggers[] = $logger;
	}

	/**
	 * Removes a logger from the registry.
	 *
	 * @param \YapepBase\Log\ILogger $logger
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
	 * @param \YapepBase\Log\Message\IMessage $message
	 */
	public function log(IMessage $message) {
		if (!$message->checkIsEmpty()) {
			foreach ($this->loggers as $logger) {
				/** @var ILogger $logger */
				$logger->log($message);
			}
		}
	}
}