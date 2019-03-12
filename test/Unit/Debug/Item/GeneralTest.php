<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use Mockery;
use YapepBase\Debug\Item\General;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class GeneralTest extends TestAbstract
{
    /** @var float */
    protected $currentTime = 1.2;

    public function testConstructor_shouldStoreGivenValues()
    {
        $name = 'name1';
        $data = ['test' => 1];

        $this->expectGetCurrentTime();
        $general = new General($name, $data);

        $this->assertSame($name, $general->getName());
        $this->assertSame($data, $general->getData());
        $this->assertSame($this->currentTime, $general->getStartTime());
        $this->assertSame(null, $general->getFinishTime());
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
