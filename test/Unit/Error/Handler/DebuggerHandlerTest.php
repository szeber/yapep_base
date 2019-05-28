<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Handler;

use Mockery\MockInterface;
use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\Debug\Item\Error as DebugErrorItem;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Error\Handler\DebuggerHandler;
use YapepBase\Exception\Exception;
use YapepBase\Test\Unit\TestAbstract;

class DebuggerHandlerTest extends TestAbstract
{
    /** @var IDataHandlerRegistry|MockInterface */
    private $debugger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->debugger = \Mockery::mock(IDataHandlerRegistry::class);
    }

    public function testHandleError_shouldAddErrorItemToDebugger()
    {
        $debuggerHandler = new DebuggerHandler($this->debugger);
        $error = new Error(1, 'message', 'file', 2, 'errorId');

        $this->expectErrorItemAddedToDebugger($error);
        $debuggerHandler->handleError($error);
    }

    public function testHandleException_shouldAddErrorItemToDebugger()
    {
        $debuggerHandler = new DebuggerHandler($this->debugger);
        $exceptionEntity = new ExceptionEntity(new Exception('message'), 'errorId');

        $this->expectErrorItemAddedToDebugger($exceptionEntity->toError());
        $debuggerHandler->handleException($exceptionEntity);
    }

    public function testHandleShutdown_shouldAddErrorItemToDebugger()
    {
        $debuggerHandler = new DebuggerHandler($this->debugger);
        $error = new Error(1, 'message', 'file', 2, 'errorId');

        $this->expectErrorItemAddedToDebugger($error);
        $debuggerHandler->handleShutdown($error);
    }

    private function expectErrorItemAddedToDebugger(Error $error): void
    {
        $this->debugger
            ->shouldReceive('addError')
            ->once()
            ->with(\Mockery::on(function (DebugErrorItem $debugErrorItem) use ($error) {
                $this->assertSame($error->getId(), $debugErrorItem->getId(), 'Error id is different');
                $this->assertSame($error->getCode(), $debugErrorItem->getCode(), 'Error code is different');
                $this->assertSame($error->getMessage(), $debugErrorItem->getMessage(), 'Error Message is different');
                $this->assertSame($error->getFile(), $debugErrorItem->getFile(), 'File is different');
                $this->assertSame($error->getLine(), $debugErrorItem->getLine(), 'Line is different');

                return true;
            }));
    }
}
