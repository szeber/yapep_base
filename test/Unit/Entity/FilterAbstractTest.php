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

    public function testGetId_shouldReturnTheSameIfFilterHasSameProperties()
    {
        $filter1 = new FilterStub();
        $filter2 = new FilterStub();

        $filter1->setTest(1);
        $filter2->setTest(1);

        $this->assertSame($filter1->getId(), $filter2->getId());
    }

    public function testGetId_shouldReturnTheDifferentIfFilterHasDifferentProperties()
    {
        $filter1 = new FilterStub();
        $filter2 = new FilterStub();

        $filter1->setTest(1);
        $filter2->setTest(2);

        $this->assertNotEquals($filter1->getId(), $filter2->getId());
    }
}
