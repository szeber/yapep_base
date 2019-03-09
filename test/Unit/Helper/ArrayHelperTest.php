<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Helper;

use YapepBase\Helper\ArrayHelper;
use YapepBase\Test\Unit\TestAbstract;

class ArrayHelperTest extends TestAbstract
{
    /** @var ArrayHelper */
    protected $arrayHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arrayHelper = new ArrayHelper();
    }

    public function intProvider()
    {
        return [
            'null exists'    => [['exists' => null], 'exists',    null, null],
            'int exists'     => [['exists' => 1],    'exists',    null, 1],
            'int not exists' => [['exists' => 1],    'notExists', null, null],
            'string exists'  => [['exists' => '1'],  'exists',    null, 1],
        ];
    }

    public function stringProvider()
    {
        return [
            'int exists'        => [['exists' => 1],   'exists',    null, '1'],
            'string not exists' => [['exists' => 1],   'notExists', null, null],
            'string exists'     => [['exists' => '1'], 'exists',    null, '1'],
        ];
    }

    public function floatProvider()
    {
        return [
            'float exists'     => [['exists' => 1.1],   'exists',    null, 1.1],
            'float not exists' => [['exists' => 1.1],   'notExists', null, null],
            'string exists'    => [['exists' => '1.1'], 'exists',    null, 1.1],
        ];
    }

    public function arrayProvider()
    {
        return [
            'array exists'     => [['exists' => [1]], 'exists',    null, [1]],
            'array not exists' => [['exists' => [1]], 'notExists', null, null],
            'string exists'    => [['exists' => 1],   'exists',    null, [1]],
        ];
    }

    public function boolProvider()
    {
        return [
            'bool exists'     => [['exists' => true], 'exists',    null, true],
            'bool not exists' => [['exists' => true], 'notExists', null, null],
            'int exists'      => [['exists' => 1],    'exists',    null, true],
        ];
    }

    /**
     * @dataProvider intProvider
     */
    public function testGetElementAsInt_shouldReturnIntOrDefaultValue(array $array, string $key, ?int $default, ?int $expectedResult)
    {
        $result = $this->arrayHelper->getElementAsInt($array, $key, $default);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider stringProvider
     */
    public function testGetElementAsString_shouldReturnIntOrDefaultValue(array $array, string $key, ?string $default, ?string $expectedResult)
    {
        $result = $this->arrayHelper->getElementAsString($array, $key, $default);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider floatProvider
     */
    public function testGetElementAsFloat_shouldReturnIntOrDefaultValue(array $array, string $key, ?float $default, ?float $expectedResult)
    {
        $result = $this->arrayHelper->getElementAsFloat($array, $key, $default);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testGetElementAsArray_shouldReturnIntOrDefaultValue(array $array, string $key, ?array $default, ?array $expectedResult)
    {
        $result = $this->arrayHelper->getElementAsArray($array, $key, $default);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider boolProvider
     */
    public function testGetElementAsBool_shouldReturnIntOrDefaultValue(array $array, string $key, ?bool $default, ?bool $expectedResult)
    {
        $result = $this->arrayHelper->getElementAsBool($array, $key, $default);

        $this->assertSame($expectedResult, $result);
    }
}
