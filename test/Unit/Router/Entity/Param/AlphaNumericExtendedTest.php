<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\AlphaNumericExtended;

class AlphaNumericExtendedTest extends TestAbstract
{
    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[AlphaNumericExtended::ARRAY_KEY_NAME => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(AlphaNumericExtended::class, $param);
    }

    public function testCreateFromArray_shouldReturnAlphaObject()
    {
        $this->assertCreateFromArrayCreatesProperObject(AlphaNumericExtended::class);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $this->assertToArrayMethodWorking(AlphaNumericExtended::class);
    }

    public function testSetState_shouldReturnObject()
    {
        $this->assertSetStateMethodWorking(AlphaNumericExtended::class);
    }

    public function patternTestProvider(): array
    {
        return [
            'accepted' => ['a1-_', 1],
            'invalid'  => ['a+1', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyAlphaCharacters(string $testString, int $matchCount)
    {
        $AlphaNumericExtended = new AlphaNumericExtended($this->name);
        $pattern      = $AlphaNumericExtended->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }
}
