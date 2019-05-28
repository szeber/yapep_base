<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Entity;

use YapepBase\Error\Entity\Error;
use YapepBase\Test\Unit\TestAbstract;

class ErrorTest extends TestAbstract
{
    public function testToString_shouldReturnString()
    {
        $error = new Error(1, 'message', 'file', 2);

        $expectedString = '[E_ERROR(1)]: message on line 2 in file';

        $this->assertSame($expectedString, (string)$error);
    }
}
