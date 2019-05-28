<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Test\Unit\TestAbstract;

class FilterAbstractTest extends TestAbstract
{
    public function testSetPageWhen0Given_shouldThrowException()
    {
        $filter = new FilterStub();

        $this->expectException(InvalidArgumentException::class);
        $filter->setPage(0);
    }

    public function testSetPage_shouldSetPage()
    {
        $filter = new FilterStub();
        $page   = 2;

        $filter->setPage($page);

        $this->assertSame($page, $filter->getPage());
    }

    public function testSetItemsPerPageWhen0Given_shouldThrowException()
    {
        $filter = new FilterStub();

        $this->expectException(InvalidArgumentException::class);
        $filter->setItemsPerPage(0);
    }

    public function testSetItemsPerPage_shouldSetItemsPerPage()
    {
        $filter       = new FilterStub();
        $itemsPerPage = 2;

        $filter->setItemsPerPage($itemsPerPage);

        $this->assertSame($itemsPerPage, $filter->getItemsPerPage());
    }
}
