<?php
declare(strict_types=1);

namespace YapepBase\Storage;

use Memcached;
use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Exception\StorageException;
use YapepBase\Helper\DateHelper;
use YapepBase\Storage\Key\IGenerator;

/**
 * Memcached storage
 */
class MemcachedStorage extends StorageAbstract implements IIncrementable
{
    /** @var Memcached */
    protected $connection;

    /** @var bool */
    protected $readOnly = false;

    /** @var DateHelper */
    protected $dateHelper;

    public function __construct(Memcached $connection, IGenerator $keyGenerator, DateHelper $dateHelper, bool $readOnly = false)
    {
        parent::__construct($keyGenerator);

        $this->connection = $connection;
        $this->dateHelper = $dateHelper;
        $this->readOnly   = $readOnly;
    }

    /**
     * Stores data the specified key
     *
     * @throws StorageException
     * @throws InvalidArgumentException
     */
    public function set(string $key, $data, int $ttlInSecondsInSecondsInSeconds = 0): void
    {
        $this->protectWhenReadOnly();

        $fullKey   = $this->keyGenerator->generate($key);
        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_SET, $fullKey, $data));

        $isStored = $this->connection->set($fullKey, $data, $ttlInSecondsInSecondsInSeconds);

        if (!$isStored) {
            $resultCode = $this->connection->getResultCode();

            if (Memcached::RES_NOTSTORED !== $resultCode) {
                throw new StorageException(
                    'Unable to store value in memcache. Error: ' . $this->connection->getResultMessage(),
                    $resultCode
                );
            }
        }

        $debugItem->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);
    }

    /**
     * Retrieves data from the cache identified by the specified key
     *
     * @throws StorageException
     */
    public function get(string $key)
    {
        $fullKey   = $this->keyGenerator->generate($key);
        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_GET, $fullKey));
        $result    = $this->connection->get($fullKey);

        if (false === $result) {
            $resultCode = $this->connection->getResultCode();
            if (Memcached::RES_NOTFOUND !== $resultCode && Memcached::RES_SUCCESS !== $resultCode) {
                throw new StorageException(
                    'Unable to get value in memcache. Error: ' . $this->connection->getResultMessage(),
                    $resultCode
                );
            }
        }

        $debugItem->setData($result)->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);

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

        $item = (new Storage($this->dateHelper, Storage::METHOD_DELETE, $key));

        $fullKey = $this->keyGenerator->generate($key);
        $this->connection->delete($fullKey);

        $item->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    /**
     * Deletes every data in the storage.
     *
     * <b>Warning!</b> Flushes the whole memcached server
     */
    public function clear(): void
    {
        $this->protectWhenReadOnly();

        $item = (new Storage($this->dateHelper, Storage::METHOD_CLEAR));

        $this->connection->flush();

        $item->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    public function increment(string $key, int $offset, int $ttlInSeconds = 0): int
    {
        $this->protectWhenReadOnly();

        $item = (new Storage($this->dateHelper, Storage::METHOD_INCREMENT, $key, $offset));

        $fullKey = $this->keyGenerator->generate($key);
        $result  = $this->connection->increment($fullKey, $offset, 0, $ttlInSeconds);

        if ($result === false) {
            throw new StorageException('Failed to increment key ' . $key);
        }

        $item->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

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
