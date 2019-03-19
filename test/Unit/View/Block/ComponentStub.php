<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block;

use YapepBase\Storage\IStorage;
use YapepBase\View\Block\ComponentAbstract;

class ComponentStub extends ComponentAbstract
{
    /** @var string */
    public $content = 'content';
    /** @var int */
    public $ttl = 12;
    /** @var string */
    public $uniqueIdentifier = 'unique';

    /** @var IStorage|null */
    protected $storage;

    public function setStorage(IStorage $storage)
    {
        $this->storage = $storage;
    }

    protected function renderContent(): void
    {
        echo $this->content;
    }

    protected function getStorage(): ?IStorage
    {
        return $this->storage;
    }

    protected function getTtlInSeconds(): int
    {
        return $this->ttl;
    }

    protected function getUniqueIdentifier(): string
    {
        return $this->uniqueIdentifier;
    }
}
