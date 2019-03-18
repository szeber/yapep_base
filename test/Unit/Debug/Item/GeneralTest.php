<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\General;

class GeneralTest extends TestAbstract
{
    public function testConstructor_shouldStoreGivenValues()
    {
        $name = 'name1';
        $data = ['test' => 1];

        $this->expectGetCurrentTime();
        $general = new General($this->dateHelper, $name, $data);

        $this->assertSame($name, $general->getName());
        $this->assertSame($data, $general->getData());
        $this->assertSame($this->currentTime, $general->getStartTime());
        $this->assertSame(null, $general->getFinishTime());
    }
}
