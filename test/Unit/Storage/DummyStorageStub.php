<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use YapepBase\Storage\DummyStorage;

class DummyStorageStub extends DummyStorage
{
    public function setSimple(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    public function getSimple(string $key)
    {
        return $this->data[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}
