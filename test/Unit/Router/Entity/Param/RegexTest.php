<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Router\Entity\Param\Regex;

class RegexTest extends TestAbstract
{
    /** @var string */
    protected $pattern = '[a-z]+';
    /** @var array */
    protected $paramData = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->paramData = [
            Regex::ARRAY_KEY_NAME    => $this->name,
            Regex::ARRAY_KEY_PATTERN => $this->pattern
        ];
    }


    public function invalidParamProvider(): array
    {
        return [
            'No name set'          => [[]],
            'Empty name set'       => [[Regex::ARRAY_KEY_NAME => '']],
            'Empty pattern set'    => [[Regex::ARRAY_KEY_NAME => '', Regex::ARRAY_KEY_PATTERN => '']],
        ];
    }

    /**
     * @dataProvider invalidParamProvider
     */
    public function testCreateFromArrayWhenInvalidDataGiven_shouldThrowException(array $param)
    {
        $this->assertCreateFromArrayValidatesProperly(Regex::class, $param);
    }

    public function testCreateFromArray_shouldReturnRegexObject()
    {
        $Regex = Regex::createFromArray($this->paramData);

        $this->assertObjectSame($Regex);
    }

    public function testToArray_shouldReturnArrayRepresentation()
    {
        $object = Regex::createFromArray($this->paramData);

        $array = $object->toArray();

        $expectedArray = [
            Regex::ARRAY_KEY_CLASS   => get_class($object),
            Regex::ARRAY_KEY_NAME    => $this->name,
            Regex::ARRAY_KEY_PATTERN => $this->pattern
        ];
        $this->assertEquals($expectedArray, $array);
    }

    public function testSetState_shouldReturnObject()
    {
        $Regex = Regex::__set_state($this->paramData);

        $this->assertObjectSame($Regex);
    }

    public function patternTestProvider(): array
    {
        return [
            'matches'  => ['first', 1],
            'no match' => ['1', 0],
        ];
    }

    /**
     * @dataProvider patternTestProvider
     */
    public function testGetPattern_shouldMatchOnlyRegexCharacters(string $testString, int $matchCount)
    {
        $Regex = new Regex($this->name, $this->pattern);
        $pattern = $Regex->getPattern();

        $this->assertSame($matchCount, preg_match('#^' . $pattern . '$#', $testString));
    }

    /**
     * @param Regex $object
     */
    protected function assertObjectSame($object)
    {
        $this->assertInstanceOf(Regex::class, $object);
        $this->assertSame($this->name, $object->getName());
        $this->assertSame($this->pattern, $object->getPattern());
    }
}
