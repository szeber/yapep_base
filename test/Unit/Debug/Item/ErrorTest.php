<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\Error;
use YapepBase\Test\Unit\TestAbstract;

class ErrorTest extends TestAbstract
{
    protected $code    = E_ERROR;
    protected $message = 'message';
    protected $file    = 'file.php';
    protected $line    = 2;
    protected $context = ['test' => 1];
    protected $id      = 'errorId';

    public function testConstructorWhenEverythingSet_shouldStoreGivenValues()
    {
        $error = new Error($this->code, $this->message, $this->file, $this->line, $this->context, $this->id);

        $this->assertSame($this->code, $error->getCode());
        $this->assertSame($this->message, $error->getMessage());
        $this->assertSame($this->file, $error->getFile());
        $this->assertSame($this->line, $error->getLine());
        $this->assertSame($this->context, $error->getContext());
        $this->assertSame($this->id, $error->getId());
    }
}
