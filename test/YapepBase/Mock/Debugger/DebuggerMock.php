<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\Debugger
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Debugger;

use YapepBase\Debugger\IDebugger;

/**
 * Mock object for debugger testing
 *
 * @package    YapepBase
 * @subpackage Mock\Debugger
 */
class DebuggerMock implements IDebugger {

	/**
	 * Stores a timing with the given name.
	 *
	 * @param string $name   The name of the timing.
	 *
	 * @return void
	 */
	public function addClockMilestone($name) {
		// TODO: Implement addClockMilestone() method.
	}

	/**
	 * Stores a memory usage with the given name.
	 *
	 * @param string $name   The name of the measure.
	 *
	 * @return void
	 */
	public function addMemoryUsageMilestone($name) {
		// TODO: Implement addMemoryUsageMilestone() method.
	}

	/**
	 * Stores a message.
	 *
	 * @param mixed $message   The message that should be stored.
	 *
	 * @return void
	 */
	public function logMessage($message) {
		// TODO: Implement logMessage() method.
	}

	/**
	 * Logs and error.
	 *
	 * @param int    $code      The code of the error.
	 * @param string $message   Error message.
	 * @param string $file      The name of the file, where the error occured.
	 * @param int    $line      The number of the line where the error occured.
	 * @param array  $context   The variables from the actual context.
	 * @param array  $trace     The backtrace for the error.
	 * @param string $id        The id of the error.
	 *
	 * @return void
	 */
	public function logError(
		$code, $message, $file = '', $line = 0, array $context = array(), array $trace = array(), $id = ''
	) {
		// TODO: Implement logError() method.
	}

	/**
	 * Logs the given query.
	 *
	 * @param string $type     The type of the query {@uses IDebugger::QUERY_TYPE_*}.
	 * @param string $query    The query string.
	 * @param mixed  $params   The params used by the query.
	 *
	 * @return int   The id of the query, which can be used to measure the execution time of it.
	 */
	public function logQuery($type, $query, $params = null) {
		// TODO: Implement logQuery() method.
	}

	/**
	 * Logs the timing of the given query.
	 *
	 * @param string $type            The type of the query {@uses IDebugger::QUERY_TYPE_*}.
	 * @param int    $queryId         The id of the query.
	 * @param float  $executionTime   The execution time of the query.
	 * @param mixed  $params          The params what used by the query.
	 *
	 * @return void
	 */
	public function logQueryExecutionTime($type, $queryId, $executionTime, $params = null) {
		// TODO: Implement logQueryExecutionTime() method.
	}

	/**
	 * Displays the interface of the Debugger (if it has one).
	 *
	 * @return void
	 */
	public function display() {
		// TODO: Implement display() method.
	}

	/**
	 * Handles the shut down event.
	 *
	 * This method should called in case of shutdown(for example fatal error).
	 *
	 * @return mixed
	 */
	public function handleShutdown() {
		// TODO: Implement handleShutdown method [emul]
	}
}