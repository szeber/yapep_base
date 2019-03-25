<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Response\Entity;

use YapepBase\Response\Entity\Header;

class HeaderTest extends TestAbstract
{
    /** @var string  */
    protected $name = 'name';
    /** @var string  */
    protected $value = 'value';
    /** @var Header */
    protected $header;

    protected function setUp(): void
    {
        parent::setUp();

        $this->header = new Header($this->name, $this->value);
    }

    public function testToStringWhenNoValueGiven_shouldReturnProperStringRepresentation()
    {
        $result = (string)(new Header($this->name));

        $this->assertSame('name', $result);
    }

    public function testToString_shouldReturnProperStringRepresentation()
    {
        $result = (string)$this->header;

        $this->assertSame('name: value', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend_shouldSendHeader()
    {
        $headers = $this->getHeadersAfterMethodCall(function () {$this->header->send();});

        $this->assertCount(1, $headers);
        $this->assertSame((string)$this->header, $headers[0]);
    }
}
