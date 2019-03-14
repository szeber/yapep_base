<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Storage;

use Mockery\MockInterface;
use YapepBase\Debug\DataHandlerRegistry;
use YapepBase\Debug\ICanReturnItems;
use YapepBase\Debug\Item\Storage;

abstract class TestAbstract extends \YapepBase\Test\Unit\TestAbstract
{
    /** @var MockInterface */
    protected $debugDataHandler;

    protected function initDebugDataHandler()
    {
        $this->debugDataHandler   = \Mockery::mock(ICanReturnItems::class);
        $debugDataHandlerRegistry = new DataHandlerRegistry();
        $debugDataHandlerRegistry->register('first', $this->debugDataHandler);
        $this->pimpleContainer->setDebugDataHandlerRegistry($debugDataHandlerRegistry);
    }

    protected function expectAddStorageDebug(string $method, ?string $key, $data = null)
    {
        $this->debugDataHandler
            ->shouldReceive('addStorage')
            ->once()
            ->with(\Mockery::on(function (Storage $item) use ($method, $key, $data) {
                return $item->getMethod() == $method
                    && $item->getKey() == $key
                    && $item->getData() == $data
                    && !empty($item->getStartTime())
                    && !empty($item->getFinishTime());
            }));
    }
}
