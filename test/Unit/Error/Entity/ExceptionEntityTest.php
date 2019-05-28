<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Entity;

use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Error\Helper\ErrorHelper;
use YapepBase\Test\Unit\TestAbstract;

class ExceptionEntityTest extends TestAbstract
{
    public function testToError_shouldReturnErrorEntity()
    {
        $message         = 'message';
        $errorId         = 'one';
        $exception       = new \Exception($message, 0);
        $exceptionEntity = new ExceptionEntity($exception, $errorId);

        $error = $exceptionEntity->toError();

        $this->assertSame(ErrorHelper::E_EXCEPTION, $error->getCode());
        $this->assertSame($message, $error->getMessage());
        $this->assertSame($exception->getFile(), $error->getFile());
        $this->assertSame($exception->getLine(), $error->getLine());
    }

    public function testToString_shouldReturnString()
    {
        $exception       = new \Exception('message', 0);
        $exceptionEntity = new ExceptionEntity($exception, 'one');

        $pattern = '#\[E_EXCEPTION\]: Unhandled Exception: message\(0\) on line \d+ in .*ExceptionEntityTest.php#';
        $this->assertRegExp($pattern, (string)$exceptionEntity);

    }
}
