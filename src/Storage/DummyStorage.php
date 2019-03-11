<?php
declare(strict_types=1);

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

    public function __construct(bool $storeValues = false)
    {
        $this->storeValues = $storeValues;
    }

    public function set(string $key, $data, int $ttlInSecondsInSeconds = 0): void
    {
        if (!$this->storeValues) {
            return;
        }

        $item = (new Storage(Storage::METHOD_SET, $key, $data))->setFinished();

        $this->data[$key] = $data;

        $this->getDebugDataHandlerRegistry()->addStorage($item);
    }

    public function get(string $key)
    {
        $data = isset($this->data[$key])
            ? $this->data[$key]
            : null;

        $item = (new Storage(Storage::METHOD_GET, $key, $data))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        return $data;
    }

    public function delete(string $key): void
    {
        $item = (new Storage(Storage::METHOD_DELETE, $key))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($item);

        unset($this->data[$key]);
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
