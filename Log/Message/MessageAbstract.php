<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Log/Message
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Log\Message;

/**
 * Log message base class
 *
 * @package    YapepBase
 * @subpackage Log/Message
 */
abstract class MessageAbstract implements IMessage {

	/**
	 * The fields of the log message.
	 *
	 * Must be a numerically indexed array with the fields added in the order they should be logged.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * The log message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * The priority. {@uses LOG_*}
	 *
	 * @var int
	 */
	protected $priority = LOG_NOTICE;

	/**
	 * Returns the fields set for the log message
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->fields;

	}

	/**
	 * Retuns the log tag
	 *
	 * @return string
	 */
	public function getMessage() {
		return (string)$this->message;
	}

	/**
	 * Returns the priority for the message
	 *
	 * @return int   {@uses LOG_*}
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Checks the object is empty or not.
	 *
	 * @return bool
	 */
	public function checkIsEmpty() {
		return empty($this->message) && empty($this->fields);
	}
}