<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Exception\StorageException;

/**
 * Interface provides the availability to increment a value stored to a key.
 */
interface IIncrementable
{
    /**
     * Increments (or decreases) the value of the key with the given offset.
     *
     * @param string $key          The key of the item to increment.
     * @param int    $offset       The amount by which to increment the item's value.
     * @param int    $ttlInSeconds The expiration time of the data in seconds if supported by the backend.
     *
     * @return int   The changed value.
     *
     * @throws StorageException
     */
    public function increment(string $key, int $offset, int $ttlInSeconds = 0): int;
}
