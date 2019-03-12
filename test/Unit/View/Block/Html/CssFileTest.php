<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\CssFile;

class CssFileTest extends TestAbstract
{
    public function testWhenNoMediaSet_shouldRenderWithoutMedia()
    {
        $cssFile = new CssFile('style.css');

        $rendered = (string)$cssFile;

        $expectedResult = '<link rel="stylesheet" type="text/css" href="style.css" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }

    public function testWhenMediaSet_shouldRenderWithMedia()
    {
        $cssFile = new CssFile('style.css', 'all');

        $rendered = (string)$cssFile;

        $expectedResult = '<link rel="stylesheet" type="text/css" href="style.css" media="all" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
