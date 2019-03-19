<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View;

use YapepBase\View\ViewAbstract;

class ViewStub extends ViewAbstract
{
    public $content = 'content';

    protected function renderContent(): void
    {
        echo $this->content;
    }
}
