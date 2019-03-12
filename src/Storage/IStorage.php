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
 * Storage interface
 */
interface IStorage
{
    /**
     * Stores data the specified key
     *
     * @param string $key    The key to be used to store the data.
     * @param mixed  $data   The data to store.
     * @param int    $ttl    The expiration time of the data in seconds if supported by the backend.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     * @throws \YapepBase\Exception\ParameterException    If TTL is set and not supported by the backend.
     */
    public function set($key, $data, $ttl = 0);

    /**
     * Retrieves data from the cache identified by the specified key. Returns FALSE if the key does not exist.
     *
     * @param string $key   The key.
     *
     * @return mixed   The data or FALSE if the specified key does not exist.
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     */
    public function get($key);

    /**
     * Deletes the data specified by the key
     *
     * @param string $key   The key.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\StorageException      On error.
     */
    public function delete($key);

    /**
     * Deletes every data in the storage.
     *
     * @return mixed
     */
    public function clear();

    /**
     * Returns if the backend is persistent or volatile.
     *
     * If the backend is volatile a system or service restart may destroy all the stored data.
     *
     * @return bool
     */
    public function isPersistent();

    /**
     * Returns whether the TTL functionality is supported by the backend.
     *
     * @return bool
     */
    public function isTtlSupported();

    /**
     * Returns TRUE if the storage backend is read only, FALSE otherwise.
     *
     * @return bool
     */
    public function isReadOnly();

    /**
     * Returns the configuration data for the storage backend. This includes the storage type as used by
     * the storage factory.
     *
     * @return array
     */
    public function getConfigData();
}
