<?php
declare(strict_types=1);

namespace YapepBase\Storage;

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

        $this->data[$key] = $data;
    }

    public function get(string $key)
    {
        return isset($this->data[$key])
            ? $this->data[$key]
            : null;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
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
