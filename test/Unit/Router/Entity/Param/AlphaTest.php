<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\Alpha;

class AlphaTest extends TestAbstract
{
    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[Alpha::ARRAY_KEY_NAME => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(Alpha::class, $param);
    }

    public function testCreateFromArray_shouldReturnAlphaObject()
    {
        $this->assertCreateFromArrayCreatesProperObject(Alpha::class);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $this->assertToArrayMethodWorking(Alpha::class);
    }

    public function testSetState_shouldReturnObject()
    {
        $this->assertSetStateMethodWorking(Alpha::class);
    }

    public function patternTestProvider(): array
    {
        return [
            'only alpha' => ['aZ', 1],
            'number'     => ['a1', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyAlphaCharacters(string $testString, int $matchCount)
    {
        $alpha   = new Alpha($this->name);
        $pattern = $alpha->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }
}
