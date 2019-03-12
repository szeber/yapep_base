<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Debug\Item\THasExecutionTime;
use YapepBase\Helper\DateHelper;

class THasExecutionTimeStub
{
    use THasExecutionTime {
        setStartTime as protected traitSetStartTime;
    }

    /** @var MockInterface */
    public $dateHelper;

    public function __construct()
    {
        $this->dateHelper = Mockery::mock(DateHelper::class);
    }

    public function setStartTime()
    {
        $this->traitSetStartTime();
    }

    protected function getDateHelper(): DateHelper
    {
        return $this->dateHelper;
    }
}
