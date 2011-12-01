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
    /** PHP Error description */
    const E_ERROR_DESCRIPTION             = 'Error';
    /** PHP Warning description */
    const E_WARNING_DESCRIPTION           = 'Warning';
    /** Parse error description */
    const E_PARSE_DESCRIPTION             = 'Parse error';
    /** PHP Notice description */
    const E_NOTICE_DESCRIPTION            = 'Notice';
    /** Core error description */
    const E_CORE_ERROR_DESCRIPTION        = 'Core error';
    /** Core warning description */
    const E_CORE_WARNING_DESCRIPTION      = 'Core warning';
    /** Compile error description */
    const E_COMPILE_ERROR_DESCRIPTION     = 'Compile error';
    /** Compile warning desription */
    const E_COMPILE_WARNING_DESCRIPTION   = 'Compile warning';
    /** User error description */
    const E_USER_ERROR_DESCRIPTION        = 'User error';
    /** User warning description */
    const E_USER_WARNING_DESCRIPTION      = 'User warning';
    /** User notice description */
    const E_USER_NOTICE_DESCRIPTION       = 'User notice';
    /** Strict error description */
    const E_STRICT_DESCRIPTION            = 'Strict error';
    /** Catchable fatal error description */
    const E_RECOVERABLE_ERROR_DESCRIPTION = 'Catchable fatal error';
    /** Deprecated description */
    const E_DEPRECATED_DESCRIPTION        = 'Deprecated';
    /** User deprecated description */
    const E_USER_DEPRECATED_DESCRIPTION   = 'User deprecated';
    /** Exception description */
    const EXCEPTION_DESCRIPTION           = 'Exception';
    /** Unknown error level description */
    const UNKNOWN_DESCRIPTION             = 'Unknown';

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
     */
    public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array());

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param Exception $exception   The exception to handle.
     * @param string $errorId        The internal ID of the error.
     */
    public function handleException(\Exception $exception, $errorId);

    /**
     * Called at script shutdown if the shutdown is because of a fatal error.
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param string $errorId      The internal ID of the error.
     */
    public function handleShutdown($errorLevel, $message, $file, $line, $errorId);
}