<?php
declare(strict_types=1);

namespace YapepBase\Storage;

use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use Memcached;

/**
 * Memcached storage
 */
class MemcachedStorage extends StorageAbstract implements IIncrementable
{
    /** @var Memcached */
    protected $connection;

    /** @var string */
    protected $keyPrefix = '';

    /** @var string */
    protected $keySuffix = '';

    /** @var bool */
    protected $hashKey = true;

    /** @var bool */
    protected $readOnly = false;

    public function __construct(Memcached $connection)
    {
        $this->connection = $connection;
    }

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self
    {
        $this->keyPrefix = $keyPrefix;
        return $this;
    }

    public function getKeySuffix(): string
    {
        return $this->keySuffix;
    }

    public function setKeySuffix(string $keySuffix): self
    {
        $this->keySuffix = $keySuffix;
        return $this;
    }

    public function isHashKey(): bool
    {
        return $this->hashKey;
    }

    public function setHashKey(bool $hashKey): self
    {
        $this->hashKey = $hashKey;
        return $this;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * Returns the key ready to be used on the backend.
     */
    protected function getFullKey(string $key): string
    {
        $key = $this->keyPrefix . $key . $this->keySuffix;
        if ($this->hashKey) {
            $key = md5($key);
        }
        return $key;
    }

    /**
     * Stores data the specified key
     *
     * @throws StorageException
     * @throws ParameterException
     */
    public function set(string $key, $data, int $ttlInSecondsInSecondsInSeconds = 0): void
    {
        $this->protectWhenReadOnly();

        $fullKey  = $this->getFullKey($key);
        $isStored = $this->connection->set($fullKey, $data, $ttlInSecondsInSecondsInSeconds);

        if (!$isStored) {
            $resultCode = $this->connection->getResultCode();

            if (Memcached::RES_NOTSTORED !== $resultCode) {
                throw new StorageException('Unable to store value in memcache. Error: ' . $this->connection->getResultMessage(),
                    $resultCode);
            }
        }
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @throws StorageException
     */
    public function get(string $key)
    {
        $fullKey = $this->getFullKey($key);
        $result  = $this->connection->get($fullKey);

        if (false === $result) {
            $resultCode = $this->connection->getResultCode();
            if (Memcached::RES_NOTFOUND !== $resultCode && Memcached::RES_SUCCESS !== $resultCode) {
                throw new StorageException('Unable to get value in memcache. Error: ' . $this->connection->getResultMessage(),
                    $resultCode);
            }
        }

        return $result;
    }

    /**
     * Deletes the data specified by the key
     *
     * @throws StorageException
     */
    public function delete(string $key): void
    {
        $this->protectWhenReadOnly();

        $fullKey = $this->getFullKey($key);
        $this->connection->delete($fullKey);
    }

    /**
     * Deletes every data in the storage.
     *
     * <b>Warning!</b> Flushes the whole memcached server
     */
    public function clear(): void
    {
        $this->protectWhenReadOnly();
        $this->connection->flush();
    }

    public function increment(string $key, int $offset, int $ttlInSeconds = 0): int
    {
        $fullKey = $this->getFullKey($key);
        $result  = $this->connection->increment($fullKey, $offset, 0, $ttlInSeconds);

        if ($result === false) {
            throw new StorageException('Failed to increment key ' . $key);
        }

        return $result;
    }

    public function isPersistent(): bool
    {
        // Memcache is cleared on restart of the memcache service, so it's never considered persistent.
        return false;
    }

    public function isTtlSupported(): bool
    {
        // Memcache has TTL support
        return true;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }
}
