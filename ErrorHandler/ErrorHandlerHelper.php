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
 * ErrorHandlerHelper class
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
class ErrorHandlerHelper {

    /** Code of the error represents an exception. (2^30) */
    const E_EXCEPTION = 1073741824;

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
    const E_EXCEPTION_DESCRIPTION           = 'Exception';
    /** Unknown error level description */
    const UNKNOWN_DESCRIPTION             = 'Unknown';

    /**
     * Returns the description for the provided error level
     *
     * @param int $errorLevel
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPhpErrorLevelDescription($errorLevel) {
        switch($errorLevel) {
            case E_ERROR:
                $description = self::E_ERROR_DESCRIPTION;
                break;

            case E_PARSE:
                $description = self::E_PARSE_DESCRIPTION;
                break;

            case E_WARNING:
                $description = self::E_WARNING_DESCRIPTION;
                break;

            case E_NOTICE:
                $description = self::E_NOTICE_DESCRIPTION;
                break;

            case E_CORE_ERROR:
                $description = self::E_CORE_ERROR_DESCRIPTION;
                break;

            case E_CORE_WARNING:
                $description = self::E_CORE_WARNING_DESCRIPTION;
                break;

            case E_COMPILE_ERROR:
                $description = self::E_COMPILE_ERROR_DESCRIPTION;
                break;

            case E_COMPILE_WARNING:
                $description = self::E_COMPILE_WARNING_DESCRIPTION;
                break;

            case E_STRICT:
                $description = self::E_STRICT_DESCRIPTION;
                break;

            case E_RECOVERABLE_ERROR:
                $description = self::E_RECOVERABLE_ERROR_DESCRIPTION;
                break;

            case E_DEPRECATED:
                $description = self::E_DEPRECATED_DESCRIPTION;
                break;

            case E_USER_ERROR:
                $description = self::E_USER_ERROR_DESCRIPTION;
                $isFatal = true;
                break;

            case E_USER_WARNING:
                $description = self::E_USER_WARNING_DESCRIPTION;
                break;

            case E_USER_NOTICE:
                $description = self::E_USER_NOTICE_DESCRIPTION;
                break;

            case E_USER_DEPRECATED:
                $description = self::E_USER_DEPRECATED_DESCRIPTION;
                break;

            case self::E_EXCEPTION:
                $description = self::E_EXCEPTION_DESCRIPTION;
                break;

            default:
                $description = self::UNKNOWN_DESCRIPTION;
                break;
        }

        return $description;
    }

    /**
     * Returns the applicable log priority for the specified errorlevel
     *
     * @param int $errorLevel
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getLogPriorityForErrorLevel($errorLevel) {
        switch($errorLevel) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $logLevel = LOG_ERR;
                break;

            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $logLevel = LOG_WARNING;
                break;

            case E_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_NOTICE:
            case E_USER_DEPRECATED:
            default:
                $logLevel = LOG_NOTICE;
                break;

        }

        return $logLevel;
    }
}