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
 * Time milestone debug item.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class TimeItem extends ItemAbstract {

	/** Field for storing the timestamp of the milestone with microsecond precision. */
	const LOCAL_FIELD_TIMESTAMP = 'timestamp';
	/** Field for storing the elapsed time since the request's start with microsecond precision. */
	const LOCAL_FIELD_ELAPSED_TIME = 'elapsedTime';

	/**
	 * Constructor.
	 *
	 * @param string $name   Name of the milestone.
	 */
	public function __construct($name) {
		$time = microtime(true);
		$debugger = Application::getInstance()->getDiContainer()->getDebugger();

		$this->data = array(
			self::FIELD_NAME               => $name,
			self::LOCAL_FIELD_TIMESTAMP    => $time,
			self::LOCAL_FIELD_ELAPSED_TIME => empty($debugger) ? null : ($time - $debugger->getStartTime()),
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
			self::FIELD_NAME               => 'Name of the milestone',
			self::LOCAL_FIELD_TIMESTAMP    => 'Creation timestamp',
			self::LOCAL_FIELD_ELAPSED_TIME => 'Time since request start',
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
		return self::DEBUG_ITEM_TIME_MILESTONE;
	}
}
