<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\View;

use YapepBase\Test\Unit\TestAbstract;

class ViewAbstractTest extends TestAbstract
{
    public function testRender_shouldCallImplementedRenderContent()
    {
        $view = new ViewStub();

        $this->expectOutputString($view->content);
        $view->render();
    }

    public function testToString_shouldReturnRenderedContent()
    {
        $view = new ViewStub();

        $result = (string)$view;

        $this->assertSame($view->content, $result);
    }
}
