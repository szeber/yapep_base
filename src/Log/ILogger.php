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

/**
 * Logger interface
 *
 * @package    YapepBase
 * @subpackage Log
 */

interface ILogger {

	/**
	 * Logs the message
	 *
	 * @param \YapepBase\Log\Message\IMessage $message   The message to log.
	 *
	 * @return void
	 */
	public function log(IMessage $message);
}