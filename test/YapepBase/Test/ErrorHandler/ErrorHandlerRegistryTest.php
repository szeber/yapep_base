<?php

namespace YapepBase\Test\ErrorHandler;

use YapepBase\ErrorHandler\ErrorHandlerRegistry;
use YapepBase\Test\Mock\ErrorHandler\ErrorHandlerMock;
use YapepBase\Exception\Exception;
use YapepBase\Application;
use YapepBase\Test\Mock\Response\ResponseMock;
use YapepBase\Config;

/**
 * Test class for LoggingErrorHandler.
 */
class ErrorHandlerRegistryTest extends \PHPUnit_Framework_TestCase {

	protected $errorReportingLevel;

	public function setUp() {
		$this->errorReportingLevel = error_reporting();
		error_reporting(E_ALL);
		Config::getInstance()->set('system.errorHandling.defaultIdTimeout', 0);
	}

	public function tearDown() {
		error_reporting($this->errorReportingLevel);
		Config::getInstance()->delete('system.errorHandling.defaultIdTimeout');
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
		$mock = new ErrorHandlerMock();
		$registry = new ErrorHandlerRegistry();
		$this->assertFalse($registry->handleError(E_WARNING, 'test', 'test.php', 1, array()),
			'The registry should return false when no error handlers are registered and an error is encountered');
		$registry->addErrorHandler($mock);
		$this->assertTrue($registry->handleError(E_WARNING, 'test', 'test.php', 1, array('test' => 'testVal')),
			'The registry should return true after handling an error');
		$this->assertSame(1, count($mock->handledErrors), 'The handled error count does not match');

		$errorData = $mock->handledErrors[0];

		$registry->handleError(E_WARNING, 'test', 'test.php', 1, array('test' => 'testVal'));

		$this->assertSame(E_WARNING, $errorData['errorLevel'], 'The error level does not match');
		$this->assertSame('test', $errorData['message'], 'The error message does not match');
		$this->assertSame('test.php', $errorData['file'], 'The file name does not match');
		$this->assertSame(1, $errorData['line'], 'The line number does not match');
		$this->assertSame(array('test' => 'testVal'), $errorData['context'], 'The context does not match');
		$this->assertEquals(__CLASS__, $errorData['backTrace'][0]['class'], 'The class in the trace does not match');
		$this->assertEquals(__FUNCTION__, $errorData['backTrace'][0]['function'],
			'The function in the trace does not match');

		$this->assertSame($mock->handledErrors[0]['errorId'], $mock->handledErrors[1]['errorId'],
			'The error IDs do not match for the same error');

		$registry->handleError(E_WARNING, 'test', 'test.php', 2, array('test' => 'testVal'));

		$this->assertFalse($mock->handledErrors[0]['errorId'] == $mock->handledErrors[2]['errorId'],
			'The errorIDs match for different errors');
	}

	/**
	 *
	 */
	public function testHandleException() {
		$mock = new ErrorHandlerMock();
		$registry = new ErrorHandlerRegistry();
		$exception = new \Exception('Test', 1);
		$registry->addErrorHandler($mock);
		$registry->handleException($exception);
		$this->assertSame(1, count($mock->handledExceptions), 'The handled exception count does not match');

		$this->assertSame($exception, $mock->handledExceptions[0]['exception'],
			'The handled exception is not the same');

		$registry->handleException($exception);

		$this->assertSame($mock->handledExceptions[0]['errorId'], $mock->handledExceptions[1]['errorId'],
			'The errorIDs do not match for the same exception');

		$exception = new \Exception('Test', 1);
		$registry->handleException($exception);

		$this->assertFalse($mock->handledExceptions[0]['errorId'] == $mock->handledExceptions[2]['errorId'],
			'The errorIDs match for different exceptions');
	}
}
