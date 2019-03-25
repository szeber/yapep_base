<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response\Entity;

use YapepBase\Exception\ParameterException;
use YapepBase\Response\Entity\Header;
use YapepBase\Response\Entity\HeaderContainer;
use YapepBase\Test\Unit\TestAbstract;

class HeaderContainerTest extends TestAbstract
{
    /** @var string */
    protected $name = 'name1';
    /** @var HeaderContainer */
    protected $headerContainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headerContainer = new HeaderContainer($this->name);
    }

    public function testAddWhenDifferentNameGiven_shouldThrowException()
    {
        $this->expectException(ParameterException::class);
        $this->headerContainer->add(new Header('differentName'));
    }

    public function testAddWhenCalled_shouldStoreGivenHeader()
    {
        $header = new Header($this->name);

        $this->headerContainer->add($header);

        $this->assertGivenHeaderExistsOnly($header);
    }

    public function testRemove_shouldRemoveOnlyTheGivenHeader()
    {
        $headerToBeRemoved = new Header($this->name);
        $headerShouldStay  = new Header($this->name, 'another');
        $this->headerContainer
            ->add($headerToBeRemoved)
            ->add($headerShouldStay);

        $this->headerContainer->remove($headerToBeRemoved);

        $this->assertGivenHeaderExistsOnly($headerShouldStay);
    }

    public function testClear_shouldRemoveEverything()
    {
        $header = new Header($this->name);
        $this->headerContainer->add($header);

        $this->headerContainer->clear();

        $this->assertEmpty($this->headerContainer->toArray());
    }

    protected function assertGivenHeaderExistsOnly(Header $header)
    {
        $retrievedHeaders = $this->headerContainer->toArray();

        $this->assertCount(1, $retrievedHeaders);
        $this->assertSame($header, array_pop($retrievedHeaders));
    }
}
