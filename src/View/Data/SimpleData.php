<?php
declare(strict_types=1);

namespace YapepBase\View\Data;

use YapepBase\View\Escape\Html;
use YapepBase\View\Escape\JavaScript;

class SimpleData extends Data
{
    const KEY_DATA = 'data';

    public function __construct($data)
    {
        parent::__construct(new Html(), new JavaScript());

        $this->set(self::KEY_DATA, $data);
    }
}
