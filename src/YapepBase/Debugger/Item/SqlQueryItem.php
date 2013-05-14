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
 * SQL query debug item.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class SqlQueryItem extends QueryItemAbstract {

	/**
	 * Returns the item's type.
	 *
	 * The type should be unique for the debug item.
	 *
	 * @return string
	 */
	public function getType() {
		return self::DEBUG_ITEM_SQL_QUERY;
	}

}
