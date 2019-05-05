<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Handler;

use YapepBase\Error\Entity\Error;
use YapepBase\Error\Handler\ExceptionCreatorHandler;
use YapepBase\Test\Unit\TestAbstract;

class ExceptionCreatorHandlerTest extends TestAbstract
{
    /** @var int */
    private $originalErrorReporting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalErrorReporting = error_reporting();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        error_reporting($this->originalErrorReporting);
    }

    public function testHandleErrorWhenErrorSuppressed_shouldDoNothing()
    {
        $handler = new ExceptionCreatorHandler();

        error_reporting(0);
        $handler->handleError(new Error(1, 'message', 'file', 2));

        $this->assertTrue(true);
    }

    public function testHandleError_shouldThrowException()
    {
        $handler        = new ExceptionCreatorHandler();
        $errorCode      = 1;
        $message        = 'message';
        $file           = 'file';
        $line           = 2;
        $error          = new Error($errorCode, $message, $file, $line);
        $errorException = new \ErrorException($message, 0, $errorCode, $file, $line);

        $this->expectExceptionObject($errorException);
        $handler->handleError($error);
    }
}
