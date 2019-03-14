<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Debug\Item\Storage;

/**
 * A dummy storage what only imitates a real storage
 */
class DummyStorage extends StorageAbstract
{
    /** @var bool */
    protected $storeValues;

    /** @var array */
    protected $data = [];

    public function __construct(IKeyGenerator $keyGenerator, bool $storeValues = false)
    {
        parent::__construct($keyGenerator);

        $this->storeValues = $storeValues;
    }

    public function set(string $key, $data, int $ttlInSecondsInSeconds = 0): void
    {
        $fullKey = $this->keyGenerator->generate($key);

        $item = (new Storage(Storage::METHOD_SET, $fullKey, $data))->setFinished();

        if ($this->storeValues) {
            $this->data[$fullKey] = $data;
        }

        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    public function get(string $key)
    {
        $fullKey = $this->keyGenerator->generate($key);

        $data = isset($this->data[$fullKey])
            ? $this->data[$fullKey]
            : null;

        $item = (new Storage(Storage::METHOD_GET, $fullKey, $data))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        return $data;
    }

    public function delete(string $key): void
    {
        $fullKey = $this->keyGenerator->generate($key);

        $item = (new Storage(Storage::METHOD_DELETE, $fullKey))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        unset($this->data[$fullKey]);
    }

    public function clear(): void
    {
        $item = (new Storage(Storage::METHOD_CLEAR))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        $this->data = [];
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function isTtlSupported(): bool
    {
        return false;
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
