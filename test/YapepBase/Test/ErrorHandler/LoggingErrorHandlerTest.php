<?php

namespace YapepBase\Test\ErrorHandler;

use YapepBase\Config;
use YapepBase\ErrorHandler\LoggingErrorHandler;
use YapepBase\Test\Mock\Log\LoggerMock;
use YapepBase\Exception\Exception;
use YapepBase\ErrorHandler\ErrorHandlerHelper;

/**
 * Test class for LoggingErrorHandler.
 */
class LoggingErrorHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \YapepBase\Test\Mock\Log\LoggerMock;
	 */
	protected $logger;

	/**
	 *
	 * @var \YapepBase\ErrorHandler\LoggingErrorHandler;
	 */
	protected $errorHandler;

	public function setUp() {
		$this->logger = new LoggerMock();
		$this->errorHandler = new LoggingErrorHandler($this->logger);
	}

	public function testHandleError() {
		$this->errorHandler->handleError(E_NOTICE, 'test', 'test', 1, array('testVar' => 'testValue'), '2', array());
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_NOTICE_DESCRIPTION, $fields[0], 'The log message type does not match');
		$this->assertSame('2', $fields[1], 'The log ID does not match');
		$this->assertSame(LOG_NOTICE, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}

	public function testHandleException() {
		$exception = new Exception('test', 1);
		$this->errorHandler->handleException($exception, '2');
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION, $fields[0], 'The log message type does not match');
		$this->assertSame('2', $fields[1], 'The log ID does not match');
		$this->assertSame(LOG_ERR, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}

	public function testHandleShutdown() {
		$this->errorHandler->handleShutdown(E_ERROR, 'test', 'test', 1, '2');
		$this->assertEquals(1, count($this->logger->loggedMessages), 'The logged messages count is not 1');
		$fields = $this->logger->loggedMessages[0]->getFields();
		$this->assertSame(ErrorHandlerHelper::E_ERROR_DESCRIPTION, $fields[0], 'The log message type does not match');
		$this->assertSame('2', $fields[1], 'The log ID does not match');
		$this->assertSame(LOG_ERR, $this->logger->loggedMessages[0]->getPriority(), 'The log level does not match');
	}
}
