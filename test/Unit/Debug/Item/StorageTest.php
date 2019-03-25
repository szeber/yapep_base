<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\Storage;
use YapepBase\Exception\ParameterException;

class StorageTest extends TestAbstract
{
    public function testConstructor_shouldStoreGivenValues()
    {
        $method = Storage::METHOD_GET;
        $key    = 'key';
        $data   = ['test' => 1];

        $this->expectGetCurrentTime();
        $storage = new Storage($this->dateHelper, $method, $key, $data);

        $this->assertSame($method, $storage->getMethod());
        $this->assertSame($key, $storage->getKey());
        $this->assertSame($data, $storage->getData());
    }

    public function testConstructorWhenInvalidMethodGiven_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        new Storage($this->dateHelper, 'invalid', 'key');
    }
}
