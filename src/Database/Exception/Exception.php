<?php
declare(strict_types=1);

namespace YapepBase\DataBase\Exception;

class Exception extends \YapepBase\Exception\Exception
{
    /** Duplicate key violation error code. */
    const ERR_DUPLICATE_KEY_VIOLATION = 23000;

    /** Numeric value out of range error code. */
    const ERR_NUMERIC_VALUE_OUT_OF_RANGE = 22003;

    public static function createByPdoException(\PDOException $exception): self
    {
        $message = '';
        $code    = 0;
        self::parsePdoException($exception, $message, $code);

        return new static($message, $code, $exception);
    }

    /**
     * @throws Exception
     */
    public static function throwByPdoException(\PDOException $exception): void
    {
        throw self::createByPdoException($exception);
    }

    /**
     * Parses the message and code from the specified PDOException.
     */
    private static function parsePdoException(\PDOException $exception, string &$message, int &$code): void
    {
        $message = $exception->getMessage();
        $code    = (int)$exception->getCode();
        $matches = [];

        // Parse the ANSI error code from the message.
        // Regex is based on the one from samuelelliot+php dot net at gmail dot com.
        if (strstr($message, 'SQLSTATE[') && preg_match('#sqlstate\[(\d+)\]:?\s(.+)$#i', $message, $matches)) {
            $message = (string)$matches[2];
            $code    = (int)$matches[1];
        }
    }
}
