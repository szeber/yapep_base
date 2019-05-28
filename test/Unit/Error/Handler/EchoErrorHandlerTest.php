<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Handler;

use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Error\Handler\EchoErrorHandler;
use YapepBase\Test\Unit\TestAbstract;

class EchoErrorHandlerTest extends TestAbstract
{
    public function testHandleError_shouldEchoStringRepresentationWithId()
    {
        $handler = new EchoErrorHandler();
        $error   = $this->getError('error', 'id');

        $this->expectOutputString('error (ID: id)' . PHP_EOL);
        $handler->handleError($error);
    }

    public function testHandleException_shouldEchoStringRepresentationWithId()
    {
        $handler   = new EchoErrorHandler();
        $exception = $this->getException('error', 'id');

        $this->expectOutputString('error (ID: id)' . PHP_EOL);
        $handler->handleException($exception);
    }

    public function testHandleShutdown_shouldEchoStringRepresentationWithId()
    {
        $handler = new EchoErrorHandler();
        $error   = $this->getError('error', 'id');

        $this->expectOutputString('error (ID: id)' . PHP_EOL);
        $handler->handleShutdown($error);
    }

    private function getError(string $errorString, string $errorId): Error
    {
        return \Mockery::mock(Error::class)
            ->shouldReceive('__toString')
                ->once()
                ->andReturn($errorString)
                ->getMock()
            ->shouldReceive('getId')
                ->once()
                ->andReturn($errorId)
                ->getMock();
    }

    private function getException(string $errorString, string $errorId): ExceptionEntity
    {
        return \Mockery::mock(ExceptionEntity::class)
            ->shouldReceive('__toString')
                ->once()
                ->andReturn($errorString)
                ->getMock()
            ->shouldReceive('getErrorId')
                ->once()
                ->andReturn($errorId)
                ->getMock();
    }
}
