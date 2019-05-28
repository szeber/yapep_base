<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Entity;

use YapepBase\Entity\FilterAbstract;

class FilterStub extends FilterAbstract
{
    private $test;

    public function setTest($test)
    {
        $this->test = $test;
    }
}
