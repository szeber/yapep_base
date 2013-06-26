<?php

namespace YapepBase\ErrorHandler;

use YapepBase\Config;
use YapepBase\ErrorHandler\DebugDataCreator;
use YapepBase\Mock\Storage\StorageMock;
use YapepBase\Exception\Exception;

/**
 * Test class for LoggingErrorHandler.
 */
class DebugDataCreatorTest extends \YapepBase\BaseTest {

	/**
	 * @var \YapepBase\Mock\Storage\StorageMock;
	 */
	protected $storage;

	/**
	 *
	 * @var \YapepBase\ErrorHandler\LoggingErrorHandler;
	 */
	protected $errorHandler;

	public function setUp() {
		parent::setUp();
		$this->storage = new StorageMock(false, true);
		$this->errorHandler = new DebugDataCreator($this->storage, true);
		$_GET = array('test' => 'getTest');
		$_POST = array('test' => 'postTest');
		$_COOKIE = array('test' => 'cookieTest');
		$_ENV = array('test' => 'envTest');
	}

	public function tearDown() {
		$_GET = array();
		$_POST = array();
		$_COOKIE = array();
		$_ENV = array();
		parent::tearDown();
	}

	public function testHandleError() {
		$this->errorHandler->handleError(E_NOTICE, 'test', 'test', 1, array('testVar' => 'testValue'), '2',
			array('test' => 'test'));
		$data = $this->storage->getData();
		$this->assertEquals(1, count($data), 'The stored data count is not 1');
		$this->assertTrue(isset($data['2']), 'No stored data');
		$this->errorHandler->handleError(E_NOTICE, 'test', 'test', 1, array('testVar' => 'testValue'), '2', array());
		$this->assertEquals(1, count($data), 'Saving debug data twice');
	}

	public function testHandleException() {
		$exception = new Exception('test', 1);
		$this->errorHandler->handleException($exception, '2');
		$data = $this->storage->getData();
		$this->assertEquals(1, count($data), 'The stored data count is not 1');
		$this->assertTrue(isset($data['2']), 'No stored data');
		$this->errorHandler->handleException($exception, '2');
		$this->assertEquals(1, count($data), 'The stored data count is not 1');
	}

	public function testHandleShutdown() {
		$this->errorHandler->handleShutdown(E_ERROR, 'test', 'test', 1, '2');
		$data = $this->storage->getData();
		$this->assertEquals(1, count($data), 'The stored data count is not 1');
		$this->assertTrue(isset($data['2']), 'No stored data');
		$this->errorHandler->handleShutdown(E_ERROR, 'test', 'test', 1, '2');
		$this->assertEquals(1, count($data), 'Saving debug data twice');
	}
}
