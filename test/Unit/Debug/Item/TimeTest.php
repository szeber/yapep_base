<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\Time;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;
use Mockery;

class TimeTest extends TestAbstract
{
    /** @var string */
    protected $name = 'name';
    /** @var float */
    protected $currentTime = 1.2;

    public function testConstructor_shouldStoreNameAndCurrentTime()
    {
        $this->expectGetCurrentTime();
        $time = new Time($this->name);

        $this->assertSame($this->name,        $time->getName());
        $this->assertSame($this->currentTime, $time->getInstantiatedAt());
    }

    public function testGetTimeElapsedSinceInitiated_shouldReturnElapsedTime()
    {
        $this->expectGetCurrentTime();
        $time = new Time($this->name);

        $result = $time->getTimeElapsedSinceInitiated(1);

        $this->assertSame(0.2, $result);
    }

    protected function expectGetCurrentTime()
    {
        $dateHelper = Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestampMs')
            ->once()
            ->andReturn($this->currentTime)
            ->getMock();

        $this->pimpleContainer[DateHelper::class] = $dateHelper;
    }
}
