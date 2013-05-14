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


/**
 * Debugger item for memory usage milestones.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class MemoryUsageItem extends ItemAbstract {

	/** Current memory usage field. */
	const LOCAL_FIELD_CURRENT = 'current';
	/** Peak memory usage field. */
	const LOCAL_FIELD_PEAK = 'peak';

	/**
	 * Constructor.
	 *
	 * @param string $name   Name of the milestone.
	 */
	public function __construct($name) {
		$this->data = array(
			self::FIELD_NAME          => $name,
			self::LOCAL_FIELD_CURRENT => memory_get_usage(true),
			self::LOCAL_FIELD_PEAK    => memory_get_peak_usage(true),
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
			self::FIELD_NAME          => 'Name of the milestone',
			self::LOCAL_FIELD_CURRENT => 'Current memory usage',
			self::LOCAL_FIELD_PEAK    => 'Peak memory usage',
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
		return self::DEBUG_ITEM_MEMORY_USAGE_MILESTONE;
	}
}
