<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\Time;

class TimeTest extends TestAbstract
{
    /** @var string */
    protected $name = 'name';

    public function testConstructor_shouldStoreNameAndCurrentTime()
    {
        $this->expectGetCurrentTime();
        $time = new Time($this->dateHelper, $this->name);

        $this->assertSame($this->name, $time->getName());
        $this->assertSame($this->currentTime, $time->getInstantiatedAt());
    }

    public function testGetTimeElapsedSinceInitiated_shouldReturnElapsedTime()
    {
        $this->expectGetCurrentTime();
        $time = new Time($this->dateHelper, $this->name);

        $result = $time->getTimeElapsedSinceInitiated(1);

        $this->assertSame(0.2, $result);
    }
}
