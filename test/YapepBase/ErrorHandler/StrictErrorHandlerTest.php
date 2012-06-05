<?php

namespace YapepBase\ErrorHandler;

use YapepBase\Config;
use YapepBase\ErrorHandler\StrictErrorHandler;
use YapepBase\Mock\Log\LoggerMock;
use YapepBase\Exception\Exception;
use \ErrorException;

/**
 * Test class for LoggingErrorHandler.
 */
class StrictErrorHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 *
	 * @var \YapepBase\ErrorHandler\StrictErrorHandler;
	 */
	protected $errorHandler;

	public function setUp() {
		$this->errorHandler = new StrictErrorHandler();
	}

	public function testHandleError() {
		try {
			$this->errorHandler->handleError(E_NOTICE, 'test', 'test', 1, array(), '2');
			$this->fail('No exception is thrown for notices');
		} catch (ErrorException $exception) {}

		try {
			$this->errorHandler->handleError(E_WARNING, 'test', 'test', 1, array(), '2');
			$this->fail('No exception is thrown for notices');
		} catch (ErrorException $exception) {}

		try {
			$this->errorHandler->handleError(E_ERROR, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_DEPRECATED, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_USER_DEPRECATED, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_USER_NOTICE, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_USER_WARNING, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_USER_ERROR, 'test', 'test', 1, array(), '2');
			$this->errorHandler->handleError(E_STRICT, 'test', 'test', 1, array(), '2');
		} catch (ErrorException $exception) {
			$this->fail('An exception is thrown for an error that\'s not a E_NOTICE or E_WARNING');
		}

	}

}
