<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   ErrorHandler
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\ErrorHandler;


/**
 * This error handler converts the desired errors to ErrorException
 *
 * <b>Warning! If you use only this ErrorHandler, errors triggered during the autoload process will be suppressed </b>
 *
 * @package      YapepBase
 * @subpackage   ErrorHandler
 */
class ExceptionCreatorErrorHandler implements IErrorHandler {

	/**
	 * The errorLevels to convert to Exception.
	 *
	 * @var int
	 */
	protected $errorLevels;

	/**
	 * Holds the current set error reporting level.
	 *
	 * @var int
	 */
	protected $errorReporting;

	/**
	 * Constructor
	 *
	 * @param int $errorLevels   Bitmask for the error levels what should be converted to Exceptions.
	 */
	public function __construct($errorLevels = null) {
		$this->errorReporting = error_reporting();

		$defaultErrorLevels =
			(
				E_ERROR
				| E_WARNING
				| E_PARSE
				| E_NOTICE
				| E_CORE_ERROR
				| E_CORE_WARNING
				| E_COMPILE_ERROR
				| E_COMPILE_WARNING
				| E_USER_ERROR
				| E_USER_WARNING
				| E_USER_NOTICE
				| E_STRICT
				| E_RECOVERABLE_ERROR
				| E_DEPRECATED
				| E_USER_DEPRECATED
			)
			& $this->errorReporting;

		$this->errorLevels = is_null($errorLevels)
			? $defaultErrorLevels
			: $defaultErrorLevels & $errorLevels;
	}

	/**
	 * Handles a PHP error
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occurred.
	 * @param int    $line         The line in the file where the error occurred.
	 * @param array  $context      The context of the error. (All variables that exist in the scope the error occurred)
	 * @param string $errorId      The internal ID of the error.
	 * @param array  $backTrace    The debug backtrace of the error.
	 *
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array()) {
		// If the error was suppressed by the @ operator
		if (error_reporting() === 0) {
			return;
		}

		if (($this->errorLevels & $errorLevel) === 0) {
			return;
		}

		throw new \ErrorException($message, 0, $errorLevel, $file, $line);
	}

	/**
	 * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
	 *
	 * @param \Exception|\Throwable $exception   The exception to handle.
	 * @param string                $errorId     The internal ID of the error.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function handleException($exception, $errorId) {
		// The method does not have to do anything
	}

	/**
	 * Called at script shutdown if the shutdown is because of a fatal error.
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occurred.
	 * @param int    $line         The line in the file where the error occurred.
	 * @param string $errorId      The internal ID of the error.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
		// We can't do anything here
	}
}
