<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Data;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Exception\ParameterException;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Data\Data;
use YapepBase\View\Escape\IEscape;

class DataTest extends TestAbstract
{
    /** @var MockInterface */
    protected $htmlEscaper;
    /** @var MockInterface */
    protected $javascriptEscaper;
    /** @var Data */
    protected $viewData;

    /** @var string */
    protected $key = 'Key';
    /** @var string */
    protected $value = 'value';
    /** @var string */
    protected $escapedValue = 'escaped';

    protected function setUp(): void
    {
        parent::setUp();
        $this->initViewData();
    }

    protected function initViewData()
    {
        $this->htmlEscaper       = Mockery::mock(IEscape::class);
        $this->javascriptEscaper = Mockery::mock(IEscape::class);
        $this->viewData          = new Data($this->htmlEscaper, $this->javascriptEscaper);
    }

    public function testSet_shouldStoreGivenValue()
    {
        $this->viewData->set($this->key, $this->value);

        $this->assertSame($this->value, $this->viewData->getRaw($this->key));
    }

    public function testSetWhenKeyAlreadyExist_shouldThrowException()
    {
        $this->viewData->set($this->key, $this->value);

        $this->expectException(ParameterException::class);
        $this->viewData->set($this->key, 'new');
    }

    public function testSetMassWhenOneKeyAlreadySet_shouldThrowException()
    {
        $this->viewData->set($this->key, $this->value);

        $mass = [
            'another'   => 1,
            $this->key  => 'new',
        ];

        $this->expectException(ParameterException::class);
        $this->viewData->setMass($mass);
    }

    public function testSetMass_shouldStoreGivenValues()
    {
        $mass = [
            'first'  => 1,
            'second' => 2,
        ];
        $this->viewData->setMass($mass);

        $this->assertSame(1, $this->viewData->getRaw('first'));
        $this->assertSame(2, $this->viewData->getRaw('second'));
    }

    public function testGetForHtmlWhenKeyNotExist_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        $this->viewData->getForHtml('notExist');
    }

    public function testGetForHtml_shouldReturnEscapedValue()
    {
        $this->viewData->set($this->key, $this->value);
        $this->expectEscapeHtml();

        $result = $this->viewData->getForHtml($this->key);

        $this->assertSame($this->escapedValue, $result);
    }

    public function testGetForHtml_shouldCacheEscapedValue()
    {
        $this->viewData->set($this->key, $this->value);
        $this->expectEscapeHtml();

        $this->viewData->getForHtml($this->key);
        $result = $this->viewData->getForHtml($this->key);

        $this->assertSame($this->escapedValue, $result);
    }

    public function testGetForJavascriptWhenKeyNotExist_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        $this->viewData->getForJavascript('notExist');
    }

    public function testGetForJavascript_shouldReturnEscapedValue()
    {
        $this->viewData->set($this->key, $this->value);
        $this->expectEscapeJavascript();

        $result = $this->viewData->getForJavascript($this->key);

        $this->assertSame($this->escapedValue, $result);
    }

    public function testGetForJavascript_shouldCacheEscapedValue()
    {
        $this->viewData->set($this->key, $this->value);
        $this->expectEscapeJavascript();

        $this->viewData->getForJavascript($this->key);
        $result = $this->viewData->getForJavascript($this->key);

        $this->assertSame($this->escapedValue, $result);
    }

    public function testGetRawWhenKeyNotExist_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        $this->viewData->getRaw('notExist');
    }

    public function testGetRaw_shouldReturnEscapedValue()
    {
        $this->viewData->set($this->key, $this->value);

        $result = $this->viewData->getRaw($this->key);

        $this->assertSame($this->value, $result);
    }

    public function testHasWhenKeyExists_shouldReturnTrue()
    {
        $this->viewData->set($this->key, $this->value);

        $result = $this->viewData->has($this->key);

        $this->assertTrue($result);
    }

    public function testHasWhenKeyDoesNotExist_shouldReturnFalse()
    {
        $result = $this->viewData->has($this->key);

        $this->assertFalse($result);
    }

    public function testClear_shouldDeleteEveryKey()
    {
        $this->viewData->set($this->key, $this->value);

        $this->viewData->clear();

        $this->assertFalse($this->viewData->has($this->key));
    }

    protected function expectEscapeHtml()
    {
        $this->htmlEscaper
            ->shouldReceive('_escape')
            ->once()
            ->with($this->value)
            ->andReturn($this->escapedValue);
    }

    protected function expectEscapeJavascript()
    {
        $this->javascriptEscaper
            ->shouldReceive('_escape')
            ->once()
            ->with($this->value)
            ->andReturn($this->escapedValue);
    }
}
