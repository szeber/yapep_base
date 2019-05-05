<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Handler;

use Mockery\MockInterface;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Error\Handler\LoggingHandler;
use YapepBase\Error\Helper\ErrorHelper;
use YapepBase\Exception\Exception;
use YapepBase\Log\ILogger;
use YapepBase\Log\Message\ErrorMessage;
use YapepBase\Test\Unit\TestAbstract;

class LoggingHandlerTest extends TestAbstract
{
    /** @var ILogger|MockInterface */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = \Mockery::mock(ILogger::class);
    }

    public function testHandleError_shouldLog()
    {
        $handler = new LoggingHandler($this->logger);
        $error = $this->getError();

        $this->expectErrorLogged($error);
        $handler->handleError($error);
    }

    public function testHandleShutdown_shouldLog()
    {
        $handler = new LoggingHandler($this->logger);
        $error = $this->getError();

        $this->expectErrorLogged($error);
        $handler->handleShutdown($error);
    }

    public function testHandleException_shouldLog()
    {
        $handler   = new LoggingHandler($this->logger);
        $exception = $this->getException();

        $this->expectExceptionLogged($exception);
        $handler->handleException($exception);
    }

    private function getError(): Error
    {
        return new Error(1, 'message', 'file', 2, 'id1');
    }

    private function getException(): ExceptionEntity
    {
        $exception = new ExceptionEntity(new Exception('test'), 'id1');

        return \Mockery::mock($exception)
            ->makePartial()
            ->shouldReceive('__toString')
            ->andReturn('string')
            ->getMock();
    }

    private function expectErrorLogged(Error $error)
    {
        $this->logger
            ->shouldReceive('log')
            ->once()
            ->with(\Mockery::on(function (ErrorMessage $errorMessage) use ($error) {
                $errorHelper  = new ErrorHelper();
                $expectedMessage = (string)$error;
                $actualMessage = $errorMessage->getMessage();
                $expectedType = $errorHelper->getDescription($error->getCode());
                $actualType = $errorMessage->getFields()[ErrorMessage::FIELD_TYPE];
                $expectedId = $error->getId();
                $actualId = $errorMessage->getFields()[ErrorMessage::FIELD_ERROR_ID];
                $expectedPriority = $errorHelper->getLogPriorityForErrorCode($error->getCode());
                $actualPriority = $errorMessage->getPriority();

                $this->assertSame($expectedMessage, $actualMessage, 'Message different');
                $this->assertSame($expectedType, $actualType, 'Type different');
                $this->assertSame($expectedId, $actualId, 'Id different');
                $this->assertSame($expectedPriority, $actualPriority, 'Priority different');
                return true;
            }));
    }

    private function expectExceptionLogged(ExceptionEntity $exception)
    {
        $this->logger
            ->shouldReceive('log')
            ->once()
            ->with(\Mockery::on(function (ErrorMessage $errorMessage) use ($exception) {
                $expectedMessage = (string)$exception;
                $actualMessage = $errorMessage->getMessage();
                $expectedType = ErrorHelper::E_EXCEPTION_DESCRIPTION;
                $actualType = $errorMessage->getFields()[ErrorMessage::FIELD_TYPE];
                $expectedId = $exception->getErrorId();
                $actualId = $errorMessage->getFields()[ErrorMessage::FIELD_ERROR_ID];
                $expectedPriority = LOG_ERR;
                $actualPriority = $errorMessage->getPriority();

                $this->assertSame($expectedMessage, $actualMessage, 'Message different');
                $this->assertSame($expectedType, $actualType, 'Type different');
                $this->assertSame($expectedId, $actualId, 'Id different');
                $this->assertSame($expectedPriority, $actualPriority, 'Priority different');
                return true;
            }));
    }
}
