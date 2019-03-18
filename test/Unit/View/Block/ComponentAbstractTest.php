<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Block;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Storage\Key\IGenerator;
use YapepBase\Storage\IStorage;
use YapepBase\Test\Unit\TestAbstract;

class ComponentAbstractTest extends TestAbstract
{
    /** @var ComponentStub */
    protected $component;
    /** @var MockInterface */
    protected $storage;
    /** @var MockInterface */
    protected $keyGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->component = new ComponentStub();
    }

    public function testRenderWhenNoStorageSet_shouldJustRender()
    {
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    public function testRenderWhenNotStoredYet_shouldRenderAndStoreOutputBeforeReturning()
    {
        $this->expectStorageUsed();
        $this->expectGetFromStorage(null);
        $this->expectSetToStorage();

        $this->component->setStorage($this->storage);
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    public function testRenderWhenStored_shouldReturnWhatStored()
    {
        $this->expectStorageUsed();
        $this->expectGetFromStorage($this->component->content);

        $this->component->setStorage($this->storage);
        $this->expectOutputString($this->component->content);
        $this->component->render();
    }

    protected function expectStorageUsed()
    {
        $this->keyGenerator = Mockery::mock(IGenerator::class)
            ->shouldReceive('setHashing')
                ->with(true)
                ->andReturn(Mockery::self())
                ->getMock()
            ->shouldReceive('setPrefix')
                ->with('component_')
                ->andReturn(Mockery::self())
                ->getMock()
            ->shouldReceive('setSuffix')
                ->with(get_class($this->component))
                ->andReturn(Mockery::self())
                ->getMock();

        $this->storage      = Mockery::mock(IStorage::class)
            ->shouldReceive('getKeyGenerator')
            ->andReturn($this->keyGenerator)
            ->getMock();
    }

    protected function expectGetFromStorage($expectedResult)
    {
        $this->storage
            ->shouldReceive('get')
            ->once()
            ->with($this->component->uniqueIdentifier)
            ->andReturn($expectedResult);
    }

    protected function expectSetToStorage()
    {
        $this->storage
            ->shouldReceive('set')
            ->once()
            ->with(
                $this->component->uniqueIdentifier,
                $this->component->content,
                $this->component->ttl
            );
    }
}
