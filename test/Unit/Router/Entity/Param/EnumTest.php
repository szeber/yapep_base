<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\Enum;

class EnumTest extends TestAbstract
{
    /** @var array */
    protected $values = ['first', 'second'];
    /** @var array */
    protected $paramData = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->paramData = [
            Enum::ARRAY_KEY_NAME   => $this->name,
            Enum::ARRAY_KEY_VALUES => $this->values
        ];
    }


    public function invalidParamProvider(): array
    {
        return [
            'No name set'          => [[]],
            'Empty name set'       => [[Enum::ARRAY_KEY_NAME => '']],
            'Empty values set'     => [[Enum::ARRAY_KEY_NAME => '', Enum::ARRAY_KEY_VALUES => []]],
            'Not array values set' => [[Enum::ARRAY_KEY_NAME => '', Enum::ARRAY_KEY_VALUES => 'invalid']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(Enum::class, $param);
    }

    public function testCreateFromArray_shouldReturnEnumObject()
    {
        $enum = Enum::createFromArray($this->paramData);

        $this->assertObjectSame($enum);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $object = Enum::createFromArray($this->paramData);

        $array = $object->toArray();

        $expectedArray = [
            Enum::ARRAY_KEY_CLASS  => get_class($object),
            Enum::ARRAY_KEY_NAME   => $this->name,
            Enum::ARRAY_KEY_VALUES => $this->values
        ];
        $this->assertEquals($expectedArray, $array);
    }

    public function testSetState_shouldReturnObject()
    {
        $enum = Enum::__set_state($this->paramData);

        $this->assertObjectSame($enum);
    }

    public function patternTestProvider(): array
    {
        return [
            'available'     => ['first', 1],
            'not available' => ['third', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyEnumCharacters(string $testString, int $matchCount)
    {
        $Enum = new Enum($this->name, $this->values);
        $pattern = $Enum->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }

    /**
     * @param Enum $object
     */
    protected function assertObjectSame($object)
    {
        $this->assertInstanceOf(Enum::class, $object);
        $this->assertSame($this->name, $object->getName());
        $this->assertSame($this->values, $object->getValues());
    }
}
