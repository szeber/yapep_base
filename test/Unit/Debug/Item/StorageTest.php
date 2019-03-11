<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\ParameterException;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;
use Mockery;

class StorageTest extends TestAbstract
{
    /** @var float */
    protected $currentTime = 1.2;

    public function testConstructor_shouldStoreGivenValues()
    {
        $method = Storage::METHOD_GET;
        $key    = 'key';
        $data   = ['test' => 1];

        $this->expectGetCurrentTime();
        $storage = new Storage($method, $key, $data);

        $this->assertSame($method, $storage->getMethod());
        $this->assertSame($key, $storage->getKey());
        $this->assertSame($data, $storage->getData());
    }

    public function testConstructorWhenInvalidMethodGiven_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        new Storage('invalid', 'key');
    }

    protected function expectGetCurrentTime()
    {
        $dateHelper = Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestampMs')
            ->once()
            ->andReturn($this->currentTime)
            ->getMock();

        $this->pimpleContainer[DateHelper::class] = $dateHelper;
    }
}
