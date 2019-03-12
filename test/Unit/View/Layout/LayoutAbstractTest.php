<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Layout;

use Mockery;
use YapepBase\Exception\ParameterException;
use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\Condition;
use YapepBase\View\Block\Html\CssFile;
use YapepBase\View\Block\Html\HttpMeta;
use YapepBase\View\Block\Html\JavaScriptFile;
use YapepBase\View\Block\Html\Link;
use YapepBase\View\Block\Html\Meta;
use YapepBase\View\IRenderable;

class LayoutAbstractTest extends TestAbstract
{
    /** @var LayoutStub */
    protected $layout;

    /** @var string */
    protected $metaName = 'meta';

    protected function setUp(): void
    {
        parent::setUp();
        $this->layout = new LayoutStub();
    }

    public function testAddMetaWhenNotExistYet_shouldAddMeta()
    {
        $meta = new Meta($this->metaName, '1');
        $this->layout->addMeta($meta);

        $expectedResult = (string)$meta;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddMetaWhenAlreadyExistAndOverwriteAllowed_shouldOverwritePrevious()
    {
        $meta1 = new Meta($this->metaName, '1');
        $meta2 = new Meta($this->metaName, '2');
        $this->layout->addMeta($meta1);
        $this->layout->addMeta($meta2);

        $expectedResult = (string)$meta2;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddMetaWhenAlreadyExistButOverwriteNotAllowed_shouldAppend()
    {
        $meta1 = new Meta($this->metaName, '1');
        $meta2 = new Meta($this->metaName, '2');
        $this->layout->addMeta($meta1);
        $this->layout->addMeta($meta2, false);

        $expectedResult = (string)(new Meta($this->metaName, '12'));

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddHttpMetaWhenNotExistYet_shouldAddHttpMeta()
    {
        $meta = new HttpMeta($this->metaName, '1');
        $this->layout->addHttpMeta($meta);

        $expectedResult = (string)$meta;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddHttpMetaWhenAlreadyExistAndOverwriteAllowed_shouldOverwritePrevious()
    {
        $meta1 = new HttpMeta($this->metaName, '1');
        $meta2 = new HttpMeta($this->metaName, '2');
        $this->layout->addHttpMeta($meta1);
        $this->layout->addHttpMeta($meta2);

        $expectedResult = (string)$meta2;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddHttpMetaWhenAlreadyExistButOverwriteNotAllowed_shouldAppend()
    {
        $meta1 = new HttpMeta($this->metaName, '1');
        $meta2 = new HttpMeta($this->metaName, '2');
        $this->layout->addHttpMeta($meta1);
        $this->layout->addHttpMeta($meta2, false);

        $expectedResult = (string)(new HttpMeta($this->metaName, '12'));

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddLink_shouldAddLink()
    {
        $link1 = new Link('hrf1', 'rel1');
        $link2 = new Link('hrf2', 'rel2');
        $this->layout->addLink($link1);
        $this->layout->addLink($link2);

        $expectedResult = (string)$link1 . (string)$link2;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddHeaderJavascript_shouldStoreFileUnderGivenCondition()
    {
        $this->layout->addHeaderJavaScriptFile('file1');
        $this->layout->addHeaderJavaScriptFile('file2', '1');
        $this->layout->addHeaderJavaScriptFile('file3', '1');

        $emptyCondition = new Condition();
        $emptyCondition->addElement(new JavaScriptFile('file1'));
        $condition = new Condition('1');
        $condition->addElement(new JavaScriptFile('file2'));
        $condition->addElement(new JavaScriptFile('file3'));

        $expectedResult = (string)$emptyCondition . (string)$condition;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddFooterJavascript_shouldStoreFileUnderGivenCondition()
    {
        $this->layout->addFooterJavaScriptFile('file1');
        $this->layout->addFooterJavaScriptFile('file2', '1');
        $this->layout->addFooterJavaScriptFile('file3', '1');

        $emptyCondition = new Condition();
        $emptyCondition->addElement(new JavaScriptFile('file1'));
        $condition = new Condition('1');
        $condition->addElement(new JavaScriptFile('file2'));
        $condition->addElement(new JavaScriptFile('file3'));

        $expectedResult = (string)$emptyCondition . (string)$condition;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testAddCss_shouldStoreFileUnderGivenCondition()
    {
        $this->layout->addCss('file1');
        $this->layout->addCss('file2', '1');
        $this->layout->addCss('file3', '1', 'print');

        $emptyCondition = new Condition();
        $emptyCondition->addElement(new CssFile('file1'));
        $condition = new Condition('1');
        $condition->addElement(new CssFile('file2'));
        $condition->addElement(new CssFile('file3', 'print'));

        $expectedResult = (string)$emptyCondition . (string)$condition;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->layout);
    }

    public function testRenderSlotWhenNotExist_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        $this->layout->renderSlot('notExist');
    }

    public function testRenderSlotWhenExist_shouldRenderGivenElements()
    {
        $slotName = 'slot1';

        $this->layout->addToSlot($slotName, $this->expectRender());
        $this->layout->addToSlot($slotName, $this->expectRender());

        $this->layout->renderSlot($slotName);
    }

    protected function expectRender(): IRenderable
    {
        return Mockery::mock(IRenderable::class)
            ->shouldReceive('render')
            ->once()
            ->getMock();
    }
}
