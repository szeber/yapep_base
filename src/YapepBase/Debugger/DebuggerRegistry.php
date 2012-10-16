<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Debugger
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Debugger;


use YapepBase\Application;
use YapepBase\Event\IEventHandler;
use YapepBase\Event\Event;

/**
 * Debugger class, that allows registering any number of DebuggerRenderers to render debugging data.
 *
 * The renderers are triggered by the Event::TYPE_APPFINISH event.
 *
 * @package    YapepBase
 * @subpackage Debugger
 */
class DebuggerRegistry implements IDebugger, IEventHandler {

	/**
	 * The HTTP Url of the stored log files.
	 *
	 * @var string
	 */
	protected $urlToLogFiles;

	/**
	 * This will be replaced with the errorId in the given HTTP Url for log files.
	 *
	 * @var string
	 */
	protected $urlParamName;

	/**
	 * The exact time of the debug console initialized(UNIX timestamp with microseconds).
	 *
	 * @var float
	 */
	protected $startTime;

	/**
	 * Array of timings with name. Stores UNIX timestamps with microseconds to the names.
	 *
	 * @var array
	 */
	protected $times = array();

	/**
	 * Array of memory usages stored by the given names. Stores a byte value to every name.
	 *
	 * @var array
	 */
	protected $memoryUsages = array();

	/**
	 * Logged messages.
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * The logged errors.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Stores whether the render() method has already been called.
	 *
	 * @var bool
	 */
	protected $isRendered = false;

	/**
	 * The logged queries.
	 *
	 * @var array
	 */
	protected $queries = array(
		self::QUERY_TYPE_DB    => array(),
		self::QUERY_TYPE_CACHE => array(),
		self::QUERY_TYPE_CURL  => array(),
	);

	/**
	 * The aggregated times of the queries.
	 *
	 * @var float
	 */
	protected $queryTimes = array(
		self::QUERY_TYPE_DB    => 0,
		self::QUERY_TYPE_CACHE => 0,
		self::QUERY_TYPE_CURL  => 0,
	);

	/**
	 * The registered renderers.
	 *
	 * @var array
	 */
	protected $renderers = array();

	/**
	 * Counters used for the logs.
	 *
	 * @var array
	 */
	protected $counters = array(
		self::COUNTER_TYPE_CACHE => array(),
		self::COUNTER_TYPE_CURL  => array(),
		self::COUNTER_TYPE_DB    => array(),
		self::COUNTER_TYPE_ERROR => array(),
	);

	/**
	 * Constructor.
	 *
	 * @param string $urlToLogFiles   The URL to the stored error log files (if there are any)
	 * @param string $urlParamName    The name of tha parameter what should be replaced with the errorId.
	 */
	public function __construct($urlToLogFiles = null, $urlParamName = null) {
		$this->urlToLogFiles = $urlToLogFiles;
		$this->urlParamName = $urlParamName;

		$this->startTime = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
	}

	/**
	 * Stores a timing with the given name.
	 *
	 * @param string $name   The name of the timing.
	 *
	 * @return void
	 */
	public function addClockMilestone($name) {
		$this->times[] = array(
			'name'    => $name,
			'elapsed' => null,
			'logged'  => microtime(true),
		);
	}

	/**
	 * Stores a memory usage with the given name.
	 *
	 * @param string $name   The name of the measure.
	 *
	 * @return void
	 */
	public function addMemoryUsageMilestone($name) {
		$this->memoryUsages[] = array(
			'name'    => $name,
			'current' => memory_get_usage(true),
			'peak'    => memory_get_peak_usage(true)
		);
	}

	/**
	 * Stores a message.
	 *
	 * @param mixed $message   The message that should be stored.
	 *
	 * @return void
	 */
	public function logMessage($message) {
		$trace = debug_backtrace(false);

		$this->messages[] = array(
			'message' => $message,
			'file'    => $trace[0]['file'],
			'line'    => $trace[0]['line'],
		);
	}

	/**
	 * Returns the full url of the error log file.
	 *
	 * @param string $errorId   The id of the error.
	 *
	 * @return string
	 */
	protected function getErrorLogUrl($errorId) {
		if (empty($this->urlToLogFiles) || empty($this->urlParamName)) {
			return '';
		}

		return str_replace($this->urlParamName, $errorId, $this->urlToLogFiles);
	}

	/**
	 * Logs and error.
	 *
	 * @param int    $code      The code of the error.
	 * @param string $message   Error message.
	 * @param string $file      The name of the file, where the error occured. If its empty,
	 *                             then it logs the file where logError() called from.
	 * @param int    $line      The number of the line where the error occured.If its <var>0</var>,
	 *                             then it logs the line where logError() called from.
	 * @param array  $context   The variables from the actual context.
	 * @param array  $trace     The backtrace for the error.
	 * @param string $id        The id of the error.
	 *
	 * @return void
	 */
	public function logError(
		$code, $message, $file = '', $line = 0, array $context = array(), array $trace = array(), $id = ''
	) {
		if (empty($trace)) {
			$trace = array();
			if (empty($file) || $line === 0) {
				$trace = debug_backtrace(false);
				$file = $trace['file'];
				$line = $trace['line'];
			}
		}

		$this->errors[] = array(
			'code'    => $code,
			'message' => $message,
			'file'    => $file,
			'line'    => $line,
			'context' => $context,
			'trace'   => $trace,
			'id'      => $id,
			'source'  => $this->getSource($file, $line),
			'logFile' => $this->getErrorLogUrl($id)
		);

		$locationId = $file . ' @ ' . $line;

		if (!isset($this->counters['error'][$locationId])) {
			$this->counters['error'][$locationId] = 1;
		}
		else {
			$this->counters['error'][$locationId]++;
		}
	}

	/**
	 * Logs the given query.
	 *
	 * @param string $type             The type of the query {@uses IDebugger::QUERY_TYPE_*}.
	 * @param string $connectionName   Connection identification string with backend type.
	 * @param string $query            The query string.
	 * @param mixed  $params           The params used by the query.
	 *
	 * @return int   The id of the query, which can be used to measure the execution time of it.
	 */
	public function logQuery($type, $connectionName, $query, $params = null) {
		$file = '?';
		$line = '?';
		$trace = debug_backtrace();

		if (isset($trace[1])) {
			$trace[1] += array('file' => '?', 'line' => '?');
			$file = $trace[1]['file'];
			$line = $trace[1]['line'];
		}

		$queryId = count($this->queries[$type]);
		$this->queries[$type][$queryId] = array(
			'file'           => $file,
			'line'           => $line,
			'query'          => $query,
			'params'         => $params,
			'runTime'        => null,
			'connectionName' => $connectionName,
		);

		$locationId = $file . ' @ ' . $line;

		switch ($type) {
			case self::QUERY_TYPE_CACHE:
				$counterType = self::COUNTER_TYPE_CACHE;
				break;

			case self::QUERY_TYPE_CURL:
				$counterType = self::COUNTER_TYPE_CURL;
				break;

			case self::QUERY_TYPE_DB:
				$counterType = self::COUNTER_TYPE_DB;
				break;

			default:
				// Unknown type
				$counterType = $type;
				break;
		}

		if (!isset($this->counters[$counterType][$locationId])) {
			$this->counters[$counterType][$locationId] = 1;
		}
		else {
			$this->counters[$counterType][$locationId]++;
		}
		return $queryId;
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
		if (!empty($params)) {
			$this->queries[$type][$queryId]['params'] = (array)$params;
		}

		$this->queries[$type][$queryId]['runTime'] = $executionTime;

		$this->queryTimes[$type] += $executionTime;
	}

	/**
	 * Adds a new renderer to the registry.
	 *
	 * @param IDebuggerRenderer $renderer   The renderer to add.
	 *
	 * @return void
	 */
	public function addRenderer(IDebuggerRenderer $renderer) {
		$this->renderers[] = $renderer;
	}

	/**
	 * Handles an event
	 *
	 * @param \YapepBase\Event\Event $event   The dispatched event.
	 *
	 * @return void
	 */
	public function handleEvent(Event $event) {
		switch ($event->getType()) {
			case Event::TYPE_APPFINISH:
				$this->render();
				break;
		}
	}

	/**
	 * Displays the interface of the Debugger (if it has one).
	 *
	 * @return void
	 */
	protected function render() {
		// We only render if we have renderers and the render() method has not been called yet.
		if ($this->isRendered || empty($this->renderers)) {
			return;
		}
		$this->isRendered = true;

		$endTime = microtime(true);
		$runTime = $endTime - $this->startTime;
		$currentMemory = memory_get_usage(true);
		$peakMemory = memory_get_peak_usage(true);
		$times = $this->times;

		// Calculate elapsed times.
		foreach ($times as $key => $time) {
			$times[$key]['elapsed'] = $time['logged'] - $this->startTime;
		}

		/** @var \YapepBase\Debugger\IDebuggerRenderer $renderer */
		foreach ($this->renderers as $renderer) {
			$renderer->render(
				$this->startTime,
				$runTime,
				$currentMemory,
				$peakMemory,
				$times,
				$this->memoryUsages,
				$this->messages,
				$this->errors,
				$this->queries,
				$this->queryTimes,
				$this->counters,
				$_SERVER,
				$_POST,
				$_GET,
				$_COOKIE,
				Application::getInstance()->getDiContainer()->getSessionRegistry()->getAllData()
			);
		}
	}

	/**
	 * Reads the given number of rows surrounding the given line of the file.
	 *
	 * @param string $file    Path of the file.
	 * @param int    $line    The number of the line.
	 * @param int    $range   Number of rows should be read before and after the given line.
	 *
	 * @return array   The rows indexed by the number of the rows.
	 */
	protected function getSource($file, $line, $range = 5) {
		$result = array();
		$buffer = file($file, FILE_IGNORE_NEW_LINES);
		if ($buffer !== false) {
			// We shift the ordinal numbers by one, to fit to the line numbers.
			array_unshift($buffer, null);
			unset($buffer[0]);
			$result = array_slice($buffer, max(0, $line - $range - 1), 2 * $range + 1, true);
		}
		return $result;
	}

	/**
	 * Handles the shut down event.
	 *
	 * This method should called in case of shutdown(for example fatal error).
	 *
	 * @return mixed
	 */
	public function handleShutdown() {
		$this->render();
	}
}