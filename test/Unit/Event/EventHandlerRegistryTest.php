<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Event;

use Mockery\MockInterface;
use YapepBase\Event\Entity\Event;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Event\IEventHandler;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class EventHandlerRegistryTest extends TestAbstract
{
    /** @var DateHelper|MockInterface */
    private $dateHelper;
    /** @var string */
    private $event = 'event';
    /** @var string */
    private $event2 = 'event2';
    /** @var IEventHandler|MockInterface */
    private $handler;
    /** @var IEventHandler|MockInterface */
    private $handler2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dateHelper = \Mockery::mock(DateHelper::class);
        $this->handler    = \Mockery::mock(IEventHandler::class);
        $this->handler2   = clone $this->handler;
    }

    public function testAddWhenNothingAddedYet_shouldAdd()
    {
        $registry = $this->getRegistry();

        $registry->add($this->event, $this->handler);
        $handlers = $registry->get($this->event);

        $this->assertCount(1, $handlers);
        $this->assertContains($this->handler, $handlers);
    }

    public function testAddWhenOneAddedAlready_shouldAdd()
    {
        $registry = $this->getRegistry();

        $registry->add($this->event, $this->handler);
        $registry->add($this->event, $this->handler2);
        $handlers = $registry->get($this->event);

        $this->assertCount(2, $handlers);
        $this->assertContains($this->handler, $handlers);
        $this->assertContains($this->handler2, $handlers);
    }

    public function testRemoveWhenNotAddedYet_shouldDoNothing()
    {
        $registry = $this->getRegistry();

        $registry->remove($this->event, $this->handler);
        $handlers = $registry->get($this->event);

        $this->assertEmpty($handlers);
    }

    public function testRemove_shouldOnlyRemoveGivenOne()
    {
        $registry = $this->getRegistry();

        $registry->add($this->event, $this->handler);
        $registry->add($this->event, $this->handler2);

        $registry->remove($this->event, $this->handler);
        $handlers = $registry->get($this->event);

        $this->assertCount(1, $handlers);
        $this->assertContains($this->handler2, $handlers);
    }

    public function testClear_shouldClearOnlyGivenEvent()
    {
        $registry = $this->getRegistry();

        $registry->add($this->event, $this->handler);
        $registry->add($this->event2, $this->handler2);

        $registry->clear($this->event);
        $handlers = $registry->getAll();

        $this->assertCount(0, $handlers[$this->event]);
        $this->assertCount(1, $handlers[$this->event2]);
        $this->assertContains($this->handler2, $handlers[$this->event2]);
    }

    public function testClearAll_shouldClearEverything()
    {
        $registry = $this->getRegistry();

        $registry->add($this->event, $this->handler);
        $registry->add($this->event2, $this->handler2);

        $registry->clearAll();
        $handlers = $registry->getAll();

        $this->assertEmpty($handlers);
    }

    public function testRaiseWhenNoHandlerAdded_shouldOnlyStoreRaiseTime()
    {
        $registry    = $this->getRegistry();
        $event       = new Event($this->event);
        $currentTime = 0.1;

        $this->expectGetCurrentTime($currentTime);

        $registry->raise($event);
        $raiseTimes = $registry->getRaiseTimes($this->event);

        $this->assertCount(1, $raiseTimes);
        $this->assertContains($currentTime, $raiseTimes);
    }

    public function testRaise_shouldUseHandlers()
    {
        $registry    = $this->getRegistry();
        $event       = new Event($this->event);
        $registry->add($this->event, $this->handler);

        $this->expectGetCurrentTime(0.1);

        $this->expectEventHandled($event);
        $registry->raise($event);
    }

    public function testIsRaisedWhenRaised_shouldReturnTrue()
    {
        $registry = $this->getRegistry();
        $event    = new Event($this->event);

        $this->expectGetCurrentTime(0.1);

        $registry->raise($event);

        $this->assertTrue($registry->isRaised($this->event));
    }

    public function testIsRaisedWhenNotRaised_shouldReturnFalse()
    {
        $registry = $this->getRegistry();

        $this->assertFalse($registry->isRaised($this->event));
    }

    private function expectGetCurrentTime(float $currentTime): void
    {
        $this->dateHelper
            ->shouldReceive('getCurrentTimestampUs')
            ->once()
            ->andReturn($currentTime);
    }

    private function expectEventHandled(Event $event): void
    {
        $this->handler
            ->shouldReceive('handleEvent')
            ->once()
            ->with($event);
    }

    private function getRegistry(): EventHandlerRegistry
    {
        return new EventHandlerRegistry($this->dateHelper);
    }
}
