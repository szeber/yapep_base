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
 * This error handler converts all warnings, notices to ErrorException.
 *
 * @package      YapepBase
 * @subpackage   ErrorHandler
 *
 * @deprecated   This class will be removed, and replaced by the ExceptionCreatorErrorHandler
 */
class StrictErrorHandler implements IErrorHandler {

	/**
	 * Handles a PHP error
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occured.
	 * @param int    $line         The line in the file where the error occured.
	 * @param array  $context      The context of the error. (All variables that exist in the scope the error occured)
	 * @param string $errorId      The internal ID of the error.
	 * @param array  $backTrace    The debug backtrace of the error.
	 *
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array()) {
		switch ($errorLevel) {
			case E_WARNING:
			case E_NOTICE:
				throw new \ErrorException($message, 0, $errorLevel, $file, $line);
				break;

		}
	}

	/**
	 * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
	 *
	 * @param \Exception $exception   The exception to handle.
	 * @param string     $errorId     The internal ID of the error.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function handleException(\Exception $exception, $errorId) {
		//noop();
	}

	/**
	 * Called at script shutdown if the shutdown is because of a fatal error.
	 *
	 * @param int    $errorLevel   The error code {@uses E_*}
	 * @param string $message      The error message.
	 * @param string $file         The file where the error occured.
	 * @param int    $line         The line in the file where the error occured.
	 * @param string $errorId      The internal ID of the error.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
		//noop();
	}
}