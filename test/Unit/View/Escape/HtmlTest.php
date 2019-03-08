<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Escape;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Exception\ParameterException;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Escape\Html;
use YapepBase\View\Escape\IEscape;

class HtmlTest extends TestAbstract
{
    /** @var Html */
    protected $html;

    protected function setUp(): void
    {
        parent::setUp();

        $this->html = new Html();
    }

    public function testEscapeWhenStringGiven_shouldReturnEscapedString()
    {
        $string = '<div>';
        $result = $this->html->__escape($string);

        $this->assertSame(htmlspecialchars($string), $result);
    }

    public function testEscapeWhenArrayGiven_shouldReturnArrayInWhichEveryElementIsEscaped()
    {
        $array = [
            '<div>',
            [
                '<div>',
                1.1,
                null
            ]
        ];

        $expectedResult = [
            htmlspecialchars('<div>'),
            [
                htmlspecialchars('<div>'),
                1.1,
                null
            ]
        ];

        $result = $this->html->__escape($array);

        $this->assertSame($expectedResult, $result);
    }

    public function nonEscapableProvider()
    {
        return [
            'int'   => [1],
            'float' => [1.1],
            'bool'  => [true],
            'null'  => [null]
        ];
    }

    /**
     * @dataProvider nonEscapableProvider
     */
    public function testEscapeWhenNonEscapableGiven_shouldReturnPassedValue($value)
    {
        $result = $this->html->__escape($value);

        $this->assertSame($value, $result);
    }

    public function testEscapeWhenObjectGivenWithPublicProperties_shouldThrowException()
    {
        $object = new \stdClass();
        $object->public = 'public';

        $this->expectException(ParameterException::class);
        $this->html->__escape($object);
    }

    public function testEscapeWhenObjectMethodCalled_shouldReturnMethodCallsResultEscaped()
    {
        $object = $this->expectMethodCalledOnObject('test', '<div>');
        $result = $this->html->__escape($object)->test();

        $this->assertSame(htmlspecialchars('<div>'), $result);
    }

    public function testEscapeWhenObjectHasSameMethodAsEscapeClass_testEscapeWhenObjectMethodCalled_shouldReturnMethodCallsResultEscaped()
    {
        $object = $this->expectMethodCalledOnObject('escapeFloat', '<div>');
        $result = $this->html->__escape($object)->escapeFloat();

        $this->assertSame(htmlspecialchars('<div>'), $result);
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
