<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Debugger\Item;

use YapepBase\Application;

/**
 * Log message debug item.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class MessageItem extends ItemAbstract implements ISourceLocatable {

	/** Message field. */
	const LOCAL_FIELD_MESSAGE = 'message';

	/**
	 * Constructor.
	 *
	 * @param string $message   The message to display.
	 */
	public function __construct($message) {
		$trace = debug_backtrace(false);

		$this->data = array(
			self::LOCAL_FIELD_MESSAGE => $message,
			self::FIELD_FILE          => $trace[0]['file'],
			self::FIELD_LINE          => $trace[0]['line'],
		);
	}

	/**
	 * Returns the field definitions as an associative array where the field name is the key,
	 * and the description is the value.
	 *
	 * @return array
	 */
	public function getFieldDefinitions() {
		return array(
			self::LOCAL_FIELD_MESSAGE => 'Message',
			self::FIELD_FILE          => 'File',
			self::FIELD_LINE          => 'Line',
		);
	}

	/**
	 * Returns the item's type.
	 *
	 * The type should be unique for the debug item.
	 *
	 * @return string
	 */
	public function getType() {
		return self::DEBUG_ITEM_MESSAGE;
	}

	/**
	 * Returns the location ID for the item's source in file @ line format.
	 *
	 * @return string
	 */
	public function getLocationId() {
		return $this->data[self::FIELD_FILE] . ' @ ' . $this->data[self::FIELD_LINE];
	}

}
