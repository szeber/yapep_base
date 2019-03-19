<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\JavaScriptFile;

class JavascriptFileTest extends TestAbstract
{
    public function testWhenRendered_shouldRenderProperly()
    {
        $jsFile = new JavaScriptFile('main.js');

        $rendered = (string)$jsFile;

        $expectedResult = '<script type="text/javascript" src="main.js"></script>';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
