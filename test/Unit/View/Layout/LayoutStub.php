<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Layout;

use YapepBase\View\Layout\LayoutAbstract;

class LayoutStub extends LayoutAbstract
{
    protected function renderContent(): void
    {
        $this->renderMetas();
        $this->renderHttpMetas();
        $this->getTitle()->render();
        $this->renderLinks();
        $this->renderStyleSheets();
        $this->renderHeaderJavaScriptFiles();
        $this->renderInnerContent();
        $this->renderFooterJavaScriptFiles();
    }

    public function renderSlot(string $name): void
    {
        parent::renderSlot($name);
    }
}
