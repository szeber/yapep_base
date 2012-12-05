<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\Log\Message
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Log\Message;


use YapepBase\Log\Message\MessageAbstract;

/**
 * Mock class for a Message.
 *
 * @package    YapepBase
 * @subpackage Mock\Log\Message
 */
class MessageMock extends MessageAbstract {

	/** Tag of the message. */
	const TAG = 'mock';

	/** Name of the first field. */
	const FIELD_1 = 'field1';
	/** Name of the second field. */
	const FIELD_2 = 'field2';

	/**
	 * Sets the message data.
	 *
	 * @param string $message   The message to log.
	 * @param string $field1    First field.
	 * @param string $field2    Second field.
	 */
	public function __construct($message, $field1, $field2) {
		$this->message = $message;
		$this->fields = array(
			self::FIELD_1 => $field1,
			self::FIELD_2 => $field2,
		);
	}

	/**
	 * Returns the log tag
	 *
	 * @return string
	 */
	public function getTag() {
		return self::TAG;
	}
}