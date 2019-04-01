<?php
declare(strict_types=1);

namespace YapepBase\View\Template;

use YapepBase\View\Data\SimpleData;

/**
 * A simple template which encodes the data in json format
 */
class JsonTemplate extends TemplateAbstract
{
    /** @var SimpleData */
    protected $data;

    public function __construct(SimpleData $data)
    {
        $this->data = $data;
    }

    protected function renderContent(): void
    {
        echo $this->data->getForJavascript(SimpleData::KEY_DATA);
    }
}
