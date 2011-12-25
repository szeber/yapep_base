<?php

namespace YapepBase\Test\ErrorHandler;

use YapepBase\ErrorHandler\ErrorHandlerRegistry;
use YapepBase\Test\Mock\ErrorHandler\ErrorHandlerMock;
use YapepBase\Exception\Exception;

/**
 * Test class for LoggingErrorHandler.
 */
class ErrorHandlerRegistryTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
    }

    public function testAddRemove() {
        $mock = new ErrorHandlerMock();
        $registry = new ErrorHandlerRegistry();
        $handlers = $registry->getErrorHandlers();
        $this->assertTrue(empty($handlers), 'Error handlers are not empty initially');
        $this->assertFalse($registry->removeErrorHandler($mock), 'Removing not set error handler fails');
        $registry->addErrorHandler($mock);
        $handlers = $registry->getErrorHandlers();
        $this->assertEquals(1, count($handlers), 'The number of registered error handlers is not 1');
        $this->assertSame($mock, $handlers[0], 'The registered error handler does not match');
        $this->assertTrue($registry->removeErrorHandler($mock), 'Removing set error handler fails');
        $handlers = $registry->getErrorHandlers();
        $this->assertTrue(empty($handlers), 'Error handlers are not empty after removal');
    }

    public function testHandleError() {
        $this->markTestIncomplete();
    }

    public function testHandleException() {
        $this->markTestIncomplete();
    }

    public function testHandleShutdown() {
        $this->markTestIncomplete();
    }
}
