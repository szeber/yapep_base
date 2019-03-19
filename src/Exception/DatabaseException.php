<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception;

/**
 * DatabaseException class
 */
class DatabaseException extends Exception
{
    /** Duplicate key violation error code. */
    const ERR_DUPLICATE_KEY_VIOLATION = 23000;

    /** Numeric value out of range error code. */
    const ERR_NUMERIC_VALUE_OUT_OF_RANGE = 22003;
}
