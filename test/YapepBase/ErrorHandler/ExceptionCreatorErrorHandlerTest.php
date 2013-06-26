<?php

namespace YapepBase\ErrorHandler;

use YapepBase\Config;
use YapepBase\ErrorHandler\ExceptionCreatorErrorHandler;
use YapepBase\Exception\Exception;
use \ErrorException;

/**
 * Test class for LoggingErrorHandler.
 */
class ExceptionCreatorErrorHandlerTest extends \YapepBase\BaseTest {

	/**
	 * The original ErrorReporting level before the test.
	 *
	 * @var int
	 */
	protected $originalErrorReporting;

	/**
	 * Sets up the testing environment for every test.
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->originalErrorReporting = error_reporting(E_ALL | E_STRICT);
	}

	/**
	 * Changes back everything to the original state.
	 *
	 * @return void
	 */
	public function tearDown() {
		error_reporting($this->originalErrorReporting);
	}

	/**
	 * Tests the handlerError() method.
	 *
	 * @return void
	 */
	public function testHandleError() {
		$errorHandler = new ExceptionCreatorErrorHandler();

		try {
			$errorHandler->handleError(E_ERROR, 'test', 'test', 1, array(), '2');
			$this->fail('Should throw an exception for an error!');
		} catch (ErrorException $exception) {}

		try {
			$errorHandler->handleError(E_STRICT, 'test', 'test', 1, array(), '2');
			$this->fail('Should throw an exception for strict error!');
		} catch (ErrorException $exception) {}

		try {
			$errorHandler->handleError(E_WARNING, 'test', 'test', 1, array(), '2');
			$this->fail('No exception is thrown for notices');
		} catch (ErrorException $exception) {}


		$errorHandler = new ExceptionCreatorErrorHandler(E_ERROR | E_WARNING | E_NOTICE);

		try {
			$errorHandler->handleError(E_STRICT, 'test', 'test', 1, array(), '2');
			$errorHandler->handleError(E_DEPRECATED, 'test', 'test', 1, array(), '2');
			$errorHandler->handleError(E_USER_DEPRECATED, 'test', 'test', 1, array(), '2');
			$errorHandler->handleError(E_USER_NOTICE, 'test', 'test', 1, array(), '2');
			$errorHandler->handleError(E_USER_WARNING, 'test', 'test', 1, array(), '2');
			$errorHandler->handleError(E_USER_ERROR, 'test', 'test', 1, array(), '2');
		} catch (ErrorException $exception) {
			$this->fail('The ErrorHandler should throw Exception only for the set ErrorLevels');
		}
	}
}
