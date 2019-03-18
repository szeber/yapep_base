<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\Event;

class EventTest extends TestAbstract
{

    public function testConstructor_shouldStoreGivenValues()
    {
        $name  = 'name';
        $data  = [1 => 2];

        $this->expectGetCurrentTime();
        $event = new Event($this->dateHelper, $name, $data);

        $this->assertSame($name, $event->getName());
        $this->assertSame($data, $event->getData());
        $this->assertSame($this->currentTime, $event->getTriggeredAt());
    }
}
