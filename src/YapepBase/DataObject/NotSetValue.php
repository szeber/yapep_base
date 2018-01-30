<?php
/**
 * @package    YapepBase
 * @subpackage DataObject
 */

namespace YapepBase\DataObject;


/**
 * Class which represents a value what has never been set.
 *
 * @package    YapepBase
 * @subpackage DataObject
 */
class NotSetValue implements \JsonSerializable {

	public function jsonSerialize() {
		return null;
	}

	public function __toString() {
		return '';
	}
}
