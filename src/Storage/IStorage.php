<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use YapepBase\Storage\Key\IGenerator;

/**
 * Storage interface
 */
interface IStorage
{
    /**
     * Returns the key generator used by the storage
     */
    public function getKeyGenerator(): IGenerator;

    /**
     * Stores data under the specified key
     *
     * @throws StorageException
     * @throws ParameterException
     */
    public function set(string $key, $data, int $ttlInSecondsInSeconds = 0): void;

    /**
     * Retrieves data identified by the specified key.
     *
     * @throws StorageException
     */
    public function get(string $key);

    /**
     * Deletes the data specified by the key
     *
     * @throws StorageException
     */
    public function delete(string $key): void;

    /**
     * Deletes every data in the storage.
     */
    public function clear(): void;

    /**
     * Returns if the backend is persistent or volatile.
     *
     * If the backend is volatile a system or service restart may destroy all the stored data.
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
