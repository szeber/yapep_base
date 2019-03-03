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

use YapepBase\Exception\ParameterException;

/**
 * Base class for debug items
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
abstract class ItemAbstract implements IDebugItem {

	/**
	 * Stores the data for this item.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Returns the item's data as an associative array.
	 *
	 * The keys must be the same as the keys returned by the getFieldDefinitions method.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Returns the specified field's data, or throws a ParameterException if the field does not exist.
	 *
	 * @param string $fieldName   Name of the field
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the field does not exist.
	 */
	public function getField($fieldName) {
		if (!array_key_exists($fieldName, $this->data)) {
			throw new ParameterException('Invalid field name: ' . $fieldName);
		}
		return $this->data[$fieldName];
	}

}
