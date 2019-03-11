<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Application;
use YapepBase\Helper\DateHelper;

abstract class ItemAbstract implements \JsonSerializable
{
    protected function getDateHelper(): DateHelper
    {
        return Application::getInstance()->getDiContainer()->get(DateHelper::class);
    }
}
