<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\Uuid;

class UuidTest extends TestAbstract
{
    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[Uuid::ARRAY_KEY_NAME => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(Uuid::class, $param);
    }

    public function testCreateFromArray_shouldReturnUuidObject()
    {
        $this->assertCreateFromArrayCreatesProperObject(Uuid::class);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $this->assertToArrayMethodWorking(Uuid::class);
    }

    public function testSetState_shouldReturnObject()
    {
        $this->assertSetStateMethodWorking(Uuid::class);
    }

    public function patternTestProvider(): array
    {
        return [
            'valid Uuid' => ['443d4376-ae13-4c5d-aa62-cb95ec48e0b6', 1],
            'invalid'    => ['1a', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyUuidCharacters(string $testString, int $matchCount)
    {
        $Uuid = new Uuid($this->name);
        $pattern = $Uuid->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }
}
