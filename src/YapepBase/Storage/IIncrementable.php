<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Storage
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Storage;

/**
 * Interface provides the availability to increment a value stored to a key.
 *
 * @package    YapepBase
 * @subpackage Storage
 */
interface IIncrementable {

	/**
	 * Increments (or decreases) the value of the key with the given offset.
	 *
	 * @param string $key      The key of the item to increment.
	 * @param int    $offset   The amount by which to increment the item's value.
	 *
	 * @return int   The changed value.
	 */
	public function increment($key, $offset);
}