<?php
declare(strict_types=1);

namespace YapepBase\Storage;

use YapepBase\Application;
use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\Exception\StorageException;
use YapepBase\Storage\Key\IGenerator;

abstract class StorageAbstract implements IStorage
{
    /** @var IGenerator */
    protected $keyGenerator;

    public function __construct(IGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    public function getKeyGenerator(): IGenerator
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
