<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Escape;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Escape\IEscape;
use YapepBase\View\Escape\JavaScript;

class JavascriptTest extends TestAbstract
{
    /** @var JavaScript */
    protected $javascript;

    protected function setUp(): void
    {
        parent::setUp();

        $this->javascript = new JavaScript();
    }

    public function primitiveProvider()
    {
        return [
            'string' => ['ű'],
            'float'  => [12.23],
            'array'  => ['a' => 'c'],
            'null'   => [null],
            'bool'   => [false]
        ];
    }

    /**
     * @dataProvider primitiveProvider
     */
    public function testEscapeWhenPrimitiveGiven_shouldJsonEncode($value)
    {
        $result = $this->javascript->_escape($value);

        $this->assertSame(json_encode($value), $result);
    }

    public function testEscapeWhenObjectMethodCalled_shouldReturnMethodCallsResultEscaped()
    {
        $string = 'ű';
        $object = $this->expectMethodCalledOnObject('test', $string);
        $result = $this->javascript->_escape($object)->test();

        $this->assertSame(json_encode($string), $result);
    }

    public function expectMethodCalledOnObject(string $methodName, string $expectedResult): MockInterface
    {
        return Mockery::mock(IEscape::class)
            ->shouldReceive($methodName)
            ->once()
            ->andReturn($expectedResult)
            ->getMock();
    }
}
