<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

abstract class ItemAbstract implements \JsonSerializable
{
    /** @var DateHelper */
    protected $dateHelper;

    public function __construct(DateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
    }

    protected function getDateHelper(): DateHelper
    {
        return $this->dateHelper;
    }
}
