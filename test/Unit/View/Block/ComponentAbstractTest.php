<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Block;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Storage\IStorage;
use YapepBase\Test\Unit\TestAbstract;

class ComponentAbstractTest extends TestAbstract
{
    /** @var ComponentStub */
    protected $component;

    /** @var MockInterface */
    protected $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->component = new ComponentStub();
        $this->storage   = Mockery::mock(IStorage::class);
    }

    public function testRenderWhenNoStorageSet_shouldJustRender()
    {
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    public function testRenderWhenNotStoredYet_shouldRenderAndStoreOutputBeforeReturning()
    {
        $this->expectGetFromStorage(null);
        $this->expectSetToStorage();

        $this->component->setStorage($this->storage);
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    public function testRenderWhenStored_shouldReturnWhatStored()
    {
        $this->expectGetFromStorage($this->component->content);

        $this->component->setStorage($this->storage);
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    protected function expectGetFromStorage($expectedResult)
    {
        $this->storage
            ->shouldReceive('get')
            ->once()
            ->with($this->getStorageKey())
            ->andReturn($expectedResult);
    }

    protected function expectSetToStorage()
    {
        $this->storage
            ->shouldReceive('set')
            ->once()
            ->with(
                $this->getStorageKey(),
                $this->component->content,
                $this->component->ttl
            );
    }

    protected function getStorageKey(): string
    {
        return 'component_' . $this->component->uniqueIdentifier . md5(get_class($this->component));
    }
}
