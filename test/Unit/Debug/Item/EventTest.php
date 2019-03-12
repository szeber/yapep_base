<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use Mockery;
use YapepBase\Debug\Item\Event;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class EventTest extends TestAbstract
{
    /** @var float */
    protected $currentTime = 0.3;

    public function testConstructor_shouldStoreGivenValues()
    {
        $name  = 'name';
        $data  = [1 => 2];

        $this->expectGetCurrentTime();
        $event = new Event($name, $data);

        $this->assertSame($name, $event->getName());
        $this->assertSame($data, $event->getData());
        $this->assertSame($this->currentTime, $event->getTriggeredAt());
    }

    protected function expectGetCurrentTime()
    {
        $dateHelper = Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestampUs')
            ->once()
            ->andReturn($this->currentTime)
            ->getMock();

        $this->pimpleContainer[DateHelper::class] = $dateHelper;
    }
}
