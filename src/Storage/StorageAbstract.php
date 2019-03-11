<?php
declare(strict_types=1);

namespace YapepBase\Storage;

use YapepBase\Exception\StorageException;

abstract class StorageAbstract implements IStorage
{
    /**
     * @throws StorageException
     */
    protected function protectWhenReadOnly(): void
    {
        if ($this->isReadOnly()) {
            throw new StorageException('Trying to write to a read only storage');
        }
    }
}
