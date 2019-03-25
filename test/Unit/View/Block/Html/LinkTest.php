<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\Link;

class LinkTest extends TestAbstract
{
    public function testWhenRendered_shouldRenderProperly()
    {
        $link = new Link('style.css', 'stylesheet');

        $rendered = (string)$link;

        $expectedResult = '<link rel="stylesheet" href="style.css" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenTypeSet_shouldRenderWithType()
    {
        $link = new Link('style.css', 'stylesheet');
        $link->setType('text/css');

        $rendered = (string)$link;

        $expectedResult = '<link rel="stylesheet" type="text/css" href="style.css" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenMediaSet_shouldRenderWithMedia()
    {
        $link = new Link('style.css', 'stylesheet');
        $link->setMedia('media');

        $rendered = (string)$link;

        $expectedResult = '<link rel="stylesheet" href="style.css" media="media" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenTitleSet_shouldRenderWithTitle()
    {
        $link = new Link('style.css', 'stylesheet');
        $link->setTitle('title');

        $rendered = (string)$link;

        $expectedResult = '<link rel="stylesheet" href="style.css" title="title" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
