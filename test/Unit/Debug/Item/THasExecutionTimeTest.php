<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Test\Unit\TestAbstract;

class THasExecutionTimeTest extends TestAbstract
{
    /** @var THasExecutionTimeStub */
    protected $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = new THasExecutionTimeStub();
    }

    public function testSetStartTime_shouldStoreActualTime()
    {
        $expectedStartTime = 0.1;
        $this->expectGetCurrentTime($expectedStartTime);

        $this->trait->setStartTime();
        $startTime = $this->trait->getStartTime();

        $this->assertSame($expectedStartTime, $startTime);
    }

    public function testSetFinished_shouldStoreActualTime()
    {
        $expectedFinishedTime = 1.1;
        $this->expectGetCurrentTime($expectedFinishedTime);

        $this->trait->setFinished();
        $finishTime = $this->trait->getFinishTime();

        $this->assertSame($expectedFinishedTime, $finishTime);
    }

    public function testGetExecutionTimeWhenFinished_shouldReturnTimeDifference()
    {
        $expectedStartTime    = 0.2;
        $expectedFinishedTime = 1.1;

        $this->expectGetCurrentTime($expectedStartTime);
        $this->trait->setStartTime();
        $this->expectGetCurrentTime($expectedFinishedTime);
        $this->trait->setFinished();

        $executionTime = $this->trait->getExecutionTime();

        $this->assertSame(0.9, $executionTime);
    }

    public function testGetExecutionTimeWhenNotFinished_shouldReturnNull()
    {
        $expectedStartTime    = 0.2;

        $this->expectGetCurrentTime($expectedStartTime);
        $this->trait->setStartTime();

        $executionTime = $this->trait->getExecutionTime();

        $this->assertNull($executionTime);
    }

    protected function expectGetCurrentTime($expectedResult)
    {
        $this->trait->dateHelper
            ->shouldReceive('getCurrentTimestampMs')
            ->once()
            ->andReturn($expectedResult)
            ->getMock();
    }
}
