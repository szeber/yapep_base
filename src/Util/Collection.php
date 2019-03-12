<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Util;

use YapepBase\Exception\ValueException;

/**
 * This is a generic Collection (array) class, which can be used as an object
 * container and overridden by child classes. Can be accessed as a native PHP
 * array.
 */
class Collection extends ArrayObject
{
    /**
     * Throws a \YapepBase\Exception\ValueException, if $offset is not an integer
     *
     * @param int $offset   The offset.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ValueException if $offset is not an integer.
     */
    final protected function keyCheck($offset)
    {
        if (!\is_int($offset)) {
            throw new ValueException($offset, 'integer');
        }
    }
}
