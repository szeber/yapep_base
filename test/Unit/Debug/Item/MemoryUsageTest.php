<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\MemoryUsage;

class MemoryUsageTest extends TestAbstract
{
    public function testConstructor_shouldStoreGivenValues()
    {
        $name = 'name';

        $memoryUsage = new MemoryUsage($name);

        $this->assertSame($name, $memoryUsage->getName());
    }
}
