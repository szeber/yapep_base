<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\Numeric;

class NumericTest extends TestAbstract
{
    public function invalidParamProvider(): array
    {
        return [
            'No name set'    => [[]],
            'Empty name set' => [[Numeric::ARRAY_KEY_NAME => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(Numeric::class, $param);
    }

    public function testCreateFromArray_shouldReturnNumericObject()
    {
        $this->assertCreateFromArrayCreatesProperObject(Numeric::class);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $this->assertToArrayMethodWorking(Numeric::class);
    }

    public function testSetState_shouldReturnObject()
    {
        $this->assertSetStateMethodWorking(Numeric::class);
    }

    public function patternTestProvider(): array
    {
        return [
            'only numeric' => ['12', 1],
            'alpha'        => ['1a', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyNumericCharacters(string $testString, int $matchCount)
    {
        $Numeric = new Numeric($this->name);
        $pattern = $Numeric->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }
}
