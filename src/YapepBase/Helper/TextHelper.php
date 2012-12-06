<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Helper
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;

/**
 * File related helper functions.
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class TextHelper {

	/**
	 * Strips slashes from the specified data.
	 *
	 * Usable to undo magic_quotes.
	 *
	 * @param mixed $data   The data to sanitize, if array (array like object) given it will be stripped recursively.
	 *
	 * @return mixed
	 */
	public static function stripSlashes($data) {
		if (is_array($data) || ($data instanceof \Iterator && $data instanceof \ArrayAccess)) {
			foreach ($data as $key => $value) {
				$data[$key] = self::stripSlashes($value);
			}
			return $data;
		} else {
			return stripslashes($data);
		}
	}
}