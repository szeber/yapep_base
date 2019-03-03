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
 * Base class for query type items.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
abstract class QueryItemAbstract extends ItemAbstract implements ISourceLocatable {

	/** Field for the query. */
	const LOCAL_FIELD_QUERY = 'query';
	/** Field for the parameters for the query. */
	const LOCAL_FIELD_PARAMS = 'params';
	/** Field for the connection's name. */
	const LOCAL_FIELD_CONNECTION_NAME = 'connectionName';
	/** Field for the backend type. */
	const LOCAL_FIELD_BACKEND_TYPE = 'backendType';
	/** Field for the execution time in seconds with microsecond precision. */
	const LOCAL_FIELD_EXECUTION_TIME = 'executionTime';

	/**
	 * Constructor.
	 *
	 * @param string $backendType      The backend's type.
	 * @param string $connectionName   Connection identification string with backend type.
	 * @param string $query            The query string.
	 * @param mixed  $params           The params used by the query.
	 * @param float  $executionTime    The execution time of the query.
	 */
	public function __construct($backendType, $connectionName, $query, $params = null, $executionTime = null) {
		$file = null;
		$line = null;
		$trace = debug_backtrace();

		if (isset($trace[1])) {
			$trace[1] += array('file' => null, 'line' => null);
			$file = $trace[1]['file'];
			$line = $trace[1]['line'];
		}

		$this->data = array(
			self::FIELD_FILE                  => $file,
			self::FIELD_LINE                  => $line,
			self::LOCAL_FIELD_QUERY           => $query,
			self::LOCAL_FIELD_PARAMS          => $params,
			self::LOCAL_FIELD_CONNECTION_NAME => $connectionName,
			self::LOCAL_FIELD_EXECUTION_TIME  => $executionTime,
			self::LOCAL_FIELD_BACKEND_TYPE    => $backendType,
		);
	}

	/**
	 * Sets the execution time of the query.
	 *
	 * @param float $time   The execution time in seconds.
	 *
	 * @return void
	 */
	public function setExecutionTime($time) {
		$this->data[self::LOCAL_FIELD_EXECUTION_TIME] = $time;
	}

	/**
	 * Returns the field definitions as an associative array where the field name is the key,
	 * and the description is the value.
	 *
	 * @return array
	 */
	public function getFieldDefinitions() {
		return array(
			self::FIELD_FILE                  => 'File',
			self::FIELD_LINE                  => 'Line',
			self::LOCAL_FIELD_QUERY           => 'Query',
			self::LOCAL_FIELD_PARAMS          => 'Params',
			self::LOCAL_FIELD_CONNECTION_NAME => 'Connection name',
			self::LOCAL_FIELD_EXECUTION_TIME  => 'Execution time',
			self::LOCAL_FIELD_BACKEND_TYPE    => 'Backend type',
		);
	}

	/**
	 * Returns the location ID for the item's source in file @ line format.
	 *
	 * @return string
	 */
	public function getLocationId() {
		return $this->data[self::FIELD_FILE] . ' @ ' . $this->data[self::FIELD_LINE];
	}
}
