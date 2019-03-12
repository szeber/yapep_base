<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Storage;

/**
 * Interface provides the availability to increment a value stored to a key.
 */
interface IIncrementable
{
    /**
     * Increments (or decreases) the value of the key with the given offset.
     *
     * @param string $key      The key of the item to increment.
     * @param int    $offset   The amount by which to increment the item's value.
     * @param int    $ttl      The expiration time of the data in seconds if supported by the backend.
     *
     * @return int   The changed value.
     */
    public function increment($key, $offset, $ttl = 0);
}
