<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\View\Block\Html\Meta;

class MetaTest extends TestAbstract
{
    public function testWhenRendered_shouldRenderProperMetaTag()
    {
        $meta = new Meta('meta1', 'value');

        $rendered = (string)$meta;

        $expectedResult = '<meta name="meta1" content="value" />';

        $this->assertSameHtmlStructure($expectedResult, $rendered);
    }
}
