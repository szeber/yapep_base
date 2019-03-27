<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Param\IParam;
use YapepBase\Router\Entity\Param\ParamAbstract;

abstract class TestAbstract extends \YapepBase\Test\Unit\TestAbstract
{
    /** @var string  */
    protected $name = 'test';

    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[ParamAbstract::ARRAY_KEY_NAME => '']],
        ];
    }

    public function assertCreateFromArrayValidatesProperly(string $testedClass, array $param)
    {
        $this->expectException(InvalidArgumentException::class);
        $testedClass::createFromArray($param);
    }

    public function assertCreateFromArrayCreatesProperObject(string $testedClass)
    {
        /** @var IParam $object */
        $object = $testedClass::createFromArray([ParamAbstract::ARRAY_KEY_NAME => $this->name]);

        $this->assertInstanceOf($testedClass, $object);
        $this->assertSame($this->name, $object->getName());
    }

    protected function assertSetStateMethodWorking(string $testedClass): void
    {
        $state = [ParamAbstract::ARRAY_KEY_NAME => $this->name];

        /** @var IParam $object */
        $object = $testedClass::__set_state($state);

        $this->assertInstanceOf($testedClass, $object);
        $this->assertSame($this->name, $object->getName());
    }

    protected function assertToArrayMethodWorking(string $testedClass): void
    {
        /** @var IParam $object */
        $object = $testedClass::createFromArray([ParamAbstract::ARRAY_KEY_NAME => $this->name]);

        $array = $object->toArray();

        $expectedArray = [
            ParamAbstract::ARRAY_KEY_CLASS => get_class($object),
            ParamAbstract::ARRAY_KEY_NAME  => $this->name,
        ];
        $this->assertEquals($expectedArray, $array);
    }
}
