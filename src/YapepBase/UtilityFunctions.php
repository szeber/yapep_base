<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase;

/**
 * UtilityFunctions class
 *
 * @package    YapepBase
 */
class UtilityFunctions {

	/**
	 * Recursively strips slashes from the specified data.
	 *
	 * Usable to undo magic_quotes.
	 *
	 * @param mixed $data   The data to sanitize
	 *
	 * @return mixed
	 */
	public static function recursiveStripSlashes($data) {
		if (is_array($data) || ($data instanceof \Iterator && $data instanceof \ArrayAccess)) {
			foreach ($data as $key => $value) {
				$data[$key] = self::recursiveStripSlashes($value);
			}
			return $data;
		} else {
			return stripslashes($data);
		}
	}
}