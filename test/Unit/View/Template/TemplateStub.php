<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\View\Template;

use YapepBase\View\Template\TemplateAbstract;

class TemplateStub extends TemplateAbstract
{
    public $content = 'Content';

    protected function renderContent(): void
    {
        echo $this->content;
    }
}
