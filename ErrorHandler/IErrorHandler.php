<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   ErrorHandler
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\ErrorHandler;

/**
 * ErrorHandler interface
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
interface IErrorHandler {
    // Error descriptions
    const E_ERROR_DESCRIPTION             = 'Error';
    const E_WARNING_DESCRIPTION           = 'Warning';
    const E_PARSE_DESCRIPTION             = 'Parse error';
    const E_NOTICE_DESCRIPTION            = 'Notice';
    const E_CORE_ERROR_DESCRIPTION        = 'Core error';
    const E_CORE_WARNING_DESCRIPTION      = 'Core warning';
    const E_COMPILE_ERROR_DESCRIPTION     = 'Compile error';
    const E_COMPILE_WARNING_DESCRIPTION   = 'Compile warning';
    const E_USER_ERROR_DESCRIPTION        = 'User error';
    const E_USER_WARNING_DESCRIPTION      = 'User warning';
    const E_USER_NOTICE_DESCRIPTION       = 'User notice';
    const E_STRICT_DESCRIPTION            = 'Strict error';
    const E_RECOVERABLE_ERROR_DESCRIPTION = 'Catchable fatal error';
    const E_DEPRECATED_DESCRIPTION        = 'Deprecated';
    const E_USER_DEPRECATED_DESCRIPTION   = 'User deprecated';
    const EXCEPTION_DESCRIPTION           = 'Exception';
    const UNKNOWN_DESCRIPTION             = 'Unknown';

    /**
     * Handles a PHP error
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param string $context      The context of the error. (All variables that exist in the scope the error occured)
     * @param string $errorId      The internal ID of the error.
     */
    function handleError($errorLevel, $message, $file, $line, $context, $errorId);

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param Exception $exception   The exception to handle.
     * @param string $errorId        The internal ID of the error.
     */
    function handleException(\Exception $exception, $errorId);

    /**
     * Called at script shutdown if the shutdown is because of a fatal error.
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param string $errorId      The internal ID of the error.
     */
    function handleShutdown($errorLevel, $message, $file, $line, $errorId);
}