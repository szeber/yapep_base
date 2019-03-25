<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Request\Source;

use YapepBase\Helper\ArrayHelper;
use YapepBase\Request\Source\Params;
use YapepBase\Test\Unit\TestAbstract;

class ParamsTest extends TestAbstract
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->pimpleContainer[ArrayHelper::class] = new ArrayHelper();
    }

    public function hasProvider()
    {
        return [
            'exists'     => [['exists' => 1], 'exists',    true],
            'not exists' => [['exists' => 1], 'notExists', false],
        ];
    }

    public function testToArray_shouldReturnSetArray()
    {
        $input = ['test' => 1];

        $result = (new Params($input))->toArray();

        $this->assertSame($input, $result);
    }

    /**
     * @dataProvider hasProvider
     */
    public function testHas_shouldReturnTrueWhenParamExists(array $input, string $name, $expectedResult)
    {
        $result = (new Params($input))->has($name);

        $this->assertSame($expectedResult, $result);
    }

    public function testGetAsInt_shouldReturnIntOrNull()
    {
        $result = (new Params(['test' => '1']))->getAsInt('test');

        $this->assertSame(1, $result);
    }

    public function testGetAsFloat_shouldReturnIntOrNull()
    {
        $result = (new Params(['test' => '1']))->getAsFloat('test');

        $this->assertSame(1.0, $result);
    }

    public function testGetAsString_shouldReturnIntOrNull()
    {
        $result = (new Params(['test' => 1]))->getAsString('test');

        $this->assertSame('1', $result);
    }

    public function testGetAsArray_shouldReturnIntOrNull()
    {
        $result = (new Params(['test' => 1]))->getAsArray('test');

        $this->assertSame([1], $result);
    }

    public function testGetAsBool_shouldReturnIntOrNull()
    {
        $result = (new Params(['test' => 1]))->getAsBool('test');

        $this->assertSame(true, $result);
    }
}
