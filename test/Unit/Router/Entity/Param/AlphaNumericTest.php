<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\AlphaNumeric;

class AlphaNumericTest extends TestAbstract
{
    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[AlphaNumeric::ARRAY_KEY_NAME => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(AlphaNumeric::class, $param);
    }

    public function testCreateFromArray_shouldReturnAlphaObject()
    {
        $this->assertCreateFromArrayCreatesProperObject(AlphaNumeric::class);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $this->assertToArrayMethodWorking(AlphaNumeric::class);
    }

    public function testSetState_shouldReturnObject()
    {
        $this->assertSetStateMethodWorking(AlphaNumeric::class);
    }

    public function patternTestProvider(): array
    {
        return [
            'alpha and numeric'    => ['a1', 1],
            'alpha and whitespace' => ['a 1', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyAlphaCharacters(string $testString, int $matchCount)
    {
        $alphaNumeric = new AlphaNumeric($this->name);
        $pattern      = $alphaNumeric->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }
}
