<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Param\Enum;
use YapepBase\Router\Entity\Param\IParam;
use YapepBase\Router\Entity\Param\Numeric;
use YapepBase\Router\Entity\Param\ParamAbstract;
use YapepBase\Router\Entity\Path;
use YapepBase\Test\Unit\TestAbstract;

class PathTest extends TestAbstract
{
    /** @var string */
    protected $pattern = '/test/{first}/{second}';
    /** @var array */
    protected $params = [];
    /** @var IParam */
    protected $param1;
    /** @var IParam */
    protected $param2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->param1 = new Numeric('first');
        $this->param2 = new Numeric('second');
        $this->params = [$this->param1, $this->param2];
    }

    public function testConstructWhenInvalidParamsGiven_shouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Path($this->pattern, [1 => 2]);
    }

    public function testSetState_shouldReturnProperObject()
    {
        $state = [
            'pattern' => $this->pattern,
            'params'  => $this->params
        ];

        $path = Path::__set_state($state);

        $this->assertSame($this->pattern, $path->getPattern());
        $this->assertSame($this->params, $path->getParams());
    }

    public function testCreateFromArrayWhenNoParamsGiven_shouldReturnPath()
    {
        $pathArray = [
            Path::ARRAY_KEY_PATTERN => $this->pattern
        ];

        $path = Path::createFromArray($pathArray);

        $this->assertSame($this->pattern, $path->getPattern());
        $this->assertEmpty($path->getParams());
    }

    public function testCreateFromArrayWhenParamClassMissing_shouldThrowException()
    {
        $paramArray = $this->param1->toArray();
        unset($paramArray[ParamAbstract::ARRAY_KEY_CLASS]);
        $pathArray  = [
            Path::ARRAY_KEY_PATTERN => $this->pattern,
            Path::ARRAY_KEY_PARAMS  => [$paramArray]
        ];

        $this->expectException(InvalidArgumentException::class);
        Path::createFromArray($pathArray);
    }

    public function testCreateFromArrayWhenParamClassInvalid_shouldThrowException()
    {
        $paramArray = $this->param1->toArray();
        $paramArray[ParamAbstract::ARRAY_KEY_CLASS] = 'invalid';
        $pathArray  = [
            Path::ARRAY_KEY_PATTERN => $this->pattern,
            Path::ARRAY_KEY_PARAMS  => [$paramArray]
        ];

        $this->expectException(InvalidArgumentException::class);
        Path::createFromArray($pathArray);
    }

    public function testCreateFromArrayWhenParamGiven_shouldAddParams()
    {
        $paramArray = $this->param1->toArray();
        $pathArray  = [
            Path::ARRAY_KEY_PATTERN => $this->pattern,
            Path::ARRAY_KEY_PARAMS  => [$paramArray]
        ];

        $path = Path::createFromArray($pathArray);

        $this->assertSame($this->pattern, $path->getPattern());
        $this->assertEquals([$this->param1], $path->getParams());
    }

    public function testToArray_shouldReturnFullArrayRepresentation()
    {
        $array = $this->getPath()->toArray();

        $expectedResult = [
            Path::ARRAY_KEY_PATTERN    => $this->pattern,
            Path::ARRAY_KEY_PARAMS     => [$this->param1->toArray(), $this->param2->toArray()],
        ];

        $this->assertSame($expectedResult, $array);
    }

    public function testGetParameterisedPathWhenPathHasDifferentNumberOfParams_shouldReturnNull()
    {
        $result = $this->getPath()->getParameterisedPath([1, 2]);

        $this->assertNull($result);
    }

    public function testGetParameterisedPathWhenPathHasDifferentParams_shouldReturnNull()
    {
        $result = $this->getPath()->getParameterisedPath(['different' => 0]);

        $this->assertNull($result);
    }

    public function testGetParameterisedPath_shouldReturnPathWhereGivenParamInserted()
    {
        $result = $this->getPath()->getParameterisedPath(['first' => 1, 'second' => 2]);

        $expectedPath = '/test/1/2';

        $this->assertSame($expectedPath, $result);
    }

    public function testGetRegexPattern_shouldReturnRegexFromPathAndParams()
    {
        $result = $this->getPath()->getRegexPattern();

        $expectedResult = '#^/test/(?P<first>[0-9]+)/(?P<second>[0-9]+)$#';

        $this->assertSame($expectedResult, $result);
    }

    public function testGetRegexPatternWhenNoParamsSet_shouldReturnRegexFromPath()
    {
        $path = new Path('/', []);
        $result = $path->getRegexPattern();

        $expectedResult = '#^/$#';

        $this->assertSame($expectedResult, $result);
    }

    protected function getPath(): Path
    {
        return new Path($this->pattern, $this->params);
    }
}
