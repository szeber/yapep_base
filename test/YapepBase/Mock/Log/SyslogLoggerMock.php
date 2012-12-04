<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\Log
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Log;


use YapepBase\Log\Message\IMessage;
use YapepBase\Log\SyslogLogger;

/**
 * Mock class for the SyslogLogger
 *
 * @package    YapepBase
 * @subpackage Mock\Log
 */
class SyslogLoggerMock extends SyslogLogger {

	/**
	 * Returns the log message prepared from the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return string
	 */
	public function getLogMessage(IMessage $message) {
		return parent::getLogMessage($message);
	}
}