<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use Mockery\MockInterface;
use YapepBase\Helper\DateHelper;

abstract class TestAbstract extends \YapepBase\Test\Unit\TestAbstract
{
    /** @var MockInterface */
    protected $dateHelper;

    /** @var float */
    protected $currentTime = 1.2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dateHelper = \Mockery::mock(DateHelper::class);
    }

    protected function expectGetCurrentTime()
    {
        $this->dateHelper
            ->shouldReceive('getCurrentTimestampUs')
            ->once()
            ->andReturn($this->currentTime)
            ->getMock();
    }
}
