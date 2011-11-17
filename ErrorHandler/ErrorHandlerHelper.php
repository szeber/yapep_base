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

    /**
     * Returns the description for the provided error level
     *
     * @param int $errorLevel
     *
     * @return string
     */
    public function getPhpErrorLevelDescription($errorLevel) {
        switch($errorLevel) {
            case E_ERROR:
                $description = IErrorHandler::E_ERROR_DESCRIPTION;
                break;

            case E_PARSE:
                $description = IErrorHandler::E_PARSE_DESCRIPTION;
                break;

            case E_WARNING:
                $description = IErrorHandler::E_WARNING_DESCRIPTION;
                break;

            case E_NOTICE:
                $description = IErrorHandler::E_NOTICE_DESCRIPTION;
                break;

            case E_CORE_ERROR:
                $description = IErrorHandler::E_CORE_ERROR_DESCRIPTION;
                break;

            case E_CORE_WARNING:
                $description = IErrorHandler::E_CORE_WARNING_DESCRIPTION;
                break;

            case E_COMPILE_ERROR:
                $description = IErrorHandler::E_COMPILE_ERROR_DESCRIPTION;
                break;

            case E_COMPILE_WARNING:
                $description = IErrorHandler::E_COMPILE_WARNING_DESCRIPTION;
                break;

            case E_STRICT:
                $description = IErrorHandler::E_STRICT_DESCRIPTION;
                break;

            case E_RECOVERABLE_ERROR:
                $description = IErrorHandler::E_RECOVERABLE_ERROR_DESCRIPTION;
                break;

            case E_DEPRECATED:
                $description = IErrorHandler::E_DEPRECATED_DESCRIPTION;
                break;

            case E_USER_ERROR:
                $description = IErrorHandler::E_USER_ERROR_DESCRIPTION;
                $isFatal = true;
                break;

            case E_USER_WARNING:
                $description = IErrorHandler::E_USER_WARNING_DESCRIPTION;
                break;

            case E_USER_NOTICE:
                $description = IErrorHandler::E_USER_NOTICE_DESCRIPTION;
                break;

            case E_USER_DEPRECATED:
                $description = IErrorHandler::E_USER_DEPRECATED_DESCRIPTION;
                break;

            default:
                $description = IErrorHandler::UNKNOWN_DESCRIPTION;
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