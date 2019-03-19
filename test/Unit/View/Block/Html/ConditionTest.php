<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Block\Html;

use YapepBase\Test\Unit\TestAbstract;
use YapepBase\Test\Unit\View\ViewStub;
use YapepBase\View\Block\Html\Condition;

class ConditionTest extends TestAbstract
{
    public function testWhenNoConditionGiven_shouldJustRenderAddedElements()
    {
        $element1          = new ViewStub();
        $element1->content = '<br />';

        $condition = new Condition();
        $condition->addElement($element1);

        $this->assertSameHtmlStructure($element1->content, (string)$condition);
    }

    public function testWhenConditionGiven_shouldRenderElementsInCondition()
    {
        $element1          = new ViewStub();
        $element1->content = '<br />';

        $element2          = new ViewStub();
        $element2->content = '<var />';

        $condition = new Condition('1=1');
        $condition->addElement($element1);
        $condition->addElement($element2);
        $expectedResult = '
            <!--[if 1=1]>
                <br /><var />
            <![endif]-->
        ';

        $this->assertSameHtmlStructure($expectedResult, (string)$condition);
    }
}
