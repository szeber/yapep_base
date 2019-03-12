<?php
declare(strict_types=1);

namespace YapepBase\Storage;

use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;

/**
 * Storage interface
 */
interface IStorage
{
    /**
     * Stores data under the specified key
     *
     * @throws StorageException     On error.
     * @throws ParameterException   If TTL is set and not supported by the backend.
     */
    public function set(string $key, $data, int $ttlInSeconds = 0): void;

    /**
     * Retrieves data from the cache identified by the specified key..
     *
     * @throws StorageException   On error.
     */
    public function get(string $key);

    /**
     * Deletes the data specified by the key
     *
     * @throws StorageException   On error.
     */
    public function delete(string $key): void;

    /**
     * Deletes every data in the storage.
     */
    public function clear(): void;

    /**
     * Returns if the backend is persistent or volatile.
     *
     * If the backend is volatile, a system or service restart may destroy all the stored data.
     */
    public function isPersistent(): bool;

    /**
     * Returns whether the TTL functionality is supported by the backend.
     */
    public function isTtlSupported(): bool;

    /**
     * Returns TRUE if the storage backend is read only, FALSE otherwise.
     */
    public function isReadOnly(): bool;
}
