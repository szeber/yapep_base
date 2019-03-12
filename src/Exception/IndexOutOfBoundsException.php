<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception;

/**
 * This exception indicates, that an index passed is invalid, because that
 * element doesn't exist.
 */
class IndexOutOfBoundsException extends \Exception
{
    /**
     * Exception constructor
     *
     * @param int|string|bool $offset   The offset in question
     */
    public function __construct($offset = false)
    {
        if ($offset !== false) {
            parent::__construct('Index out of bounds: ' . $offset);
        } else {
            parent::__construct('Index out of bounds');
        }
    }
}
