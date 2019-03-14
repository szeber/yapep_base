<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

use YapepBase\Application;
use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\Exception\StorageException;

abstract class StorageAbstract implements IStorage
{
    /** @var IKeyGenerator */
    protected $keyGenerator;

    public function __construct(IKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    public function getKeyGenerator(): IKeyGenerator
    {
        return $this->keyGenerator;
    }

    /**
     * @throws StorageException
     */
    protected function protectWhenReadOnly(): void
    {
        if ($this->isReadOnly()) {
            throw new StorageException('Trying to write to a read only storage');
        }
    }

    protected function getDebugDataHandlerRegistry(): IDataHandlerRegistry
    {
        return Application::getInstance()->getDiContainer()->getDebugDataHandlerRegistry();
    }
}
