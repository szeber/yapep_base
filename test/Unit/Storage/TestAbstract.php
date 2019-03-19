<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\Debug\DataHandlerRegistry;
use YapepBase\Debug\ICanReturnItems;
use YapepBase\Debug\Item\Storage;
use YapepBase\Helper\DateHelper;

abstract class TestAbstract extends \YapepBase\Test\Unit\TestAbstract
{
    /** @var MockInterface */
    protected $debugDataHandler;
    /** @var MockInterface */
    protected $dateHelper;
    /** @var float */
    protected $debugStartedAt  = 1.0;
    /** @var float */
    protected $debugFinishedAt = 2.0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dateHelper = \Mockery::mock(DateHelper::class);
    }

    protected function initDebugDataHandler()
    {
        $this->debugDataHandler   = \Mockery::mock(ICanReturnItems::class);
        $debugDataHandlerRegistry = new DataHandlerRegistry(new DateHelper());
        $debugDataHandlerRegistry->register('first', $this->debugDataHandler);
        $this->pimpleContainer->setDebugDataHandlerRegistry($debugDataHandlerRegistry);
    }

    protected function expectDebugTimesRetrieved()
    {
        $this->dateHelper
            ->shouldReceive('getCurrentTimestampUs')
            ->andReturn($this->debugStartedAt, $this->debugFinishedAt)
            ->getMock();
    }

    protected function expectAddStorageDebug(
        string $method,
        ?string $key,
        $data = null,
        ?float $startedAt = null,
        ?float $finishedAt = null
    ) {
        $this->debugDataHandler
            ->shouldReceive('addStorage')
            ->once()
            ->with(\Mockery::on(function (Storage $item) use ($method, $key, $data, $startedAt, $finishedAt) {
                return $item->getMethod() == $method
                    && $item->getKey() == $key
                    && $item->getData() == $data
                    && $item->getStartTime() === $startedAt
                    && $item->getFinishTime() === $finishedAt;
            }));
    }
}
