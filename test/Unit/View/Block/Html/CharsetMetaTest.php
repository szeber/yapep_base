<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\CharsetMeta;

class CharsetMetaTest extends TestAbstract
{
    public function testWhenRendered_shouldRenderProperMetaCharsetTag()
    {
        $charsetMeta = new CharsetMeta('utf8');

        $rendered = (string)$charsetMeta;

        $expectedResult = '<meta charset="utf8" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
