<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\Title;

class TitleTest extends TestAbstract
{
    public function testWhenNoTitleGiven_shouldRenderNothing()
    {
        $title = new Title();

        $rendered = (string)$title;

        $expectedResult = '';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenOneTitleGiven_shouldRenderTitle()
    {
        $title = new Title();
        $title->setTitle('Full title');

        $rendered = (string)$title;

        $expectedResult = '<title>Full title</title>';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenOneTitlePrepended_shouldPrependGivenTitle()
    {
        $title = new Title();
        $title->setTitle('Full title');
        $title->prependToTitle('Prepend');

        $rendered = (string)$title;

        $expectedResult = '<title>Prepend - Full title</title>';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenOneTitleAppended_shouldAppendGivenTitle()
    {
        $title = new Title();
        $title->setTitle('Full title');
        $title->appendToTitle('Append');

        $rendered = (string)$title;

        $expectedResult = '<title>Full title - Append</title>';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenSeparatorChanged_shouldUseGivenSeparatorToRender()
    {
        $title = new Title();
        $title->setTitle('Full title');
        $title->appendToTitle('Append');
        $title->prependToTitle('Prepend');
        $title->setSeparator('|');

        $rendered = (string)$title;

        $expectedResult = '<title>Prepend|Full title|Append</title>';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
