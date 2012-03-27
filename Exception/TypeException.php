<?php
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Janos Pasztor <net@janoszen.hu>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Exception;

/**
 * This Exception states, that an invalid type was provided.
 */
class TypeException extends Exception {
	/**
	 *
	 * @param mixed $object the object which does not match the required type
	 * @param string $required the type required
	 */
	function __construct($object, $required = "") {
		$type = 'unknown';
		switch (\gettype($object)) {
			case 'object':
				$type = \get_class($object);
				break;
			default:
				$type = \gettype($object);
				break;
		}
		$message = 'Invalid object type: ' . $type;
		if ($required) {
			$message .= ' expected ' . $required;
		}
		parent::__construct($message);
	}
}
