<?php
declare(strict_types=1);

namespace YapepBase\Error\Helper;

use YapepBase\Error\Entity\Error;

class ErrorHelper
{
    /** Code of the error represents an exception. (2^30) */
    const E_EXCEPTION = 1073741824;

    const E_ERROR_DESCRIPTION             = 'E_ERROR';
    const E_WARNING_DESCRIPTION           = 'E_WARNING';
    const E_PARSE_DESCRIPTION             = 'E_PARSE';
    const E_NOTICE_DESCRIPTION            = 'E_NOTICE';
    const E_CORE_ERROR_DESCRIPTION        = 'E_CORE_ERROR';
    const E_CORE_WARNING_DESCRIPTION      = 'E_CORE_WARNING';
    const E_COMPILE_ERROR_DESCRIPTION     = 'E_COMPILE_ERROR';
    const E_COMPILE_WARNING_DESCRIPTION   = 'E_COMPILE_WARNINING';
    const E_USER_ERROR_DESCRIPTION        = 'E_USER_ERROR';
    const E_USER_WARNING_DESCRIPTION      = 'E_USER_WARNING';
    const E_USER_NOTICE_DESCRIPTION       = 'E_USER_NOTICE';
    const E_STRICT_DESCRIPTION            = 'E_STRICT';
    const E_RECOVERABLE_ERROR_DESCRIPTION = 'E_RECOVERABLE_ERROR';
    const E_DEPRECATED_DESCRIPTION        = 'E_DEPRECATED';
    const E_USER_DEPRECATED_DESCRIPTION   = 'E_USER_DEPRECATED';
    const E_EXCEPTION_DESCRIPTION         = 'E_EXCEPTION';
    const UNKNOWN_DESCRIPTION             = 'UNKNOWN';

    public function getDescription(int $errorCode): string
    {
        switch ($errorCode) {
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

    public function getLogPriorityForErrorCode(int $errorCode): int
    {
        switch ($errorCode) {
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

    public function isFatal(int $errorCode): bool
    {
        switch ($errorCode) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                return true;
                break;
        }

        return false;
    }

    public function getLastError(): ?Error
    {
        $error = error_get_last();

        return empty($error)
            ? null
            : new Error((int)$error['type'], $error['message'], $error['file'], (int)$error['line']);
    }

    public function log(string $message): void
    {
        error_log($message, 4);
    }

    public function exit(): void
    {
        exit;
    }
}
