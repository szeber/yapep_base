<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Debug\Item\Storage;
use YapepBase\Helper\DateHelper;
use YapepBase\Storage\Key\IGenerator;

/**
 * A dummy storage what only imitates a real storage
 */
class DummyStorage extends StorageAbstract
{
    /** @var bool */
    protected $storeValues;

    /** @var array */
    protected $data = [];

    /** @var DateHelper */
    protected $dateHelper;

    public function __construct(IGenerator $keyGenerator, DateHelper $dateHelper, bool $storeValues = false)
    {
        parent::__construct($keyGenerator);

        $this->dateHelper = $dateHelper;
        $this->storeValues = $storeValues;
    }

    public function set(string $key, $data, int $ttlInSecondsInSeconds = 0): void
    {
        $fullKey = $this->keyGenerator->generate($key);

        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_SET, $fullKey, $data))->setFinished();

        if ($this->storeValues) {
            $this->data[$fullKey] = $data;
        }

        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);
    }

    public function get(string $key)
    {
        $fullKey = $this->keyGenerator->generate($key);

        $data = isset($this->data[$fullKey])
            ? $this->data[$fullKey]
            : null;

        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_GET, $fullKey, $data))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);

        return $data;
    }

    public function delete(string $key): void
    {
        $fullKey = $this->keyGenerator->generate($key);

        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_DELETE, $fullKey))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);

        unset($this->data[$fullKey]);
    }

    public function clear(): void
    {
        $debugItem = (new Storage($this->dateHelper, Storage::METHOD_CLEAR))->setFinished();
        $this->getDebugDataHandlerRegistry()->addStorage($debugItem);

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
