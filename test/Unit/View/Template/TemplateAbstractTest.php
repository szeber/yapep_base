<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Template;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\Test\Unit\View\Layout\LayoutStub;

class TemplateAbstractTest extends TestAbstract
{
    /** @var TemplateStub */
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->template = new TemplateStub();
    }

    public function testRenderWhenNoLayoutSet_shouldJustRenderTemplate()
    {
        $this->expectOutputString($this->template->content);
        $this->template->render();
    }

    public function testRenderWhenLayoutSet_shouldRenderTemplateIntoLayout()
    {
        $layout = new LayoutStub();
        $title  = $layout->getTitle();
        $title->setTitle('Title');

        $this->template->setLayout($layout);

        $expectedResult = (string)$title . $this->template->content;

        $this->assertSameHtmlStructure($expectedResult, (string)$this->template);
    }
}
