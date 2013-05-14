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
 * Interface for the debug items.
 *
 * Also contains the definitions for the built in items and some common field names.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
interface IDebugItem {

	/** Log message item type. */
	const DEBUG_ITEM_MESSAGE = 'message';
	/** Error item type. */
	const DEBUG_ITEM_ERROR = 'error';
	/** SQL query item type. */
	const DEBUG_ITEM_SQL_QUERY = 'sqlQuery';
	/** Cache request item type. */
	const DEBUG_ITEM_STORAGE = 'storage';
	/** Curl request item type. */
	const DEBUG_ITEM_CURL_REQUEST = 'curlRequest';
	/** Time milestone item type. */
	const DEBUG_ITEM_TIME_MILESTONE = 'time';
	/** Memory milestone item type. */
	const DEBUG_ITEM_MEMORY_USAGE_MILESTONE = 'memoryUsage';

	/** Name field. Used to return a human readable identified for the item. */
	const FIELD_NAME = 'name';
	/** File field. Used to describe which file the item originated in. */
	const FIELD_FILE = 'file';
	/** Line field. Used to describe which line the item originated in. Used together with FIELD_FILE. */
	const FIELD_LINE = 'line';
	/** Execution time field. Used to describe the execution time of a debug item. */
	const FIELD_EXECUTION_TIME = 'executionTime';

	/**
	 * Returns the field definitions as an associative array where the field name is the key,
	 * and the description is the value.
	 *
	 * @return array
	 */
	public function getFieldDefinitions();

	/**
	 * Returns the item's data as an associative array.
	 *
	 * The keys must be the same as the keys returned by the getFieldDefinitions method.
	 *
	 * @return array
	 */
	public function getData();

	/**
	 * Returns the item's type.
	 *
	 * The type should be unique for the debug item.
	 *
	 * @return string
	 */
	public function getType();
}
