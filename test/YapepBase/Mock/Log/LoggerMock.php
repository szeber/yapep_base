<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Log
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Log;

use YapepBase\Log\ILogger;
use YapepBase\Log\Message\IMessage;

/**
 * LoggerMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Log
 * @codeCoverageIgnore
 */
class LoggerMock implements ILogger {

	/**
	 * Stores the logged messages.
	 *
	 * @var array
	 */
	public $loggedMessages = array();

	/**
	 * Logs the message
	 *
	 * @param IMessage $message
	 */
	public function log(IMessage $message) {
		$this->loggedMessages[] = $message;
	}
}
