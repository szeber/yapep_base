<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;

use YapepBase\Application;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Event\Event;
use YapepBase\Mock\Batch\BatchScriptMock;

/**
 * Test for the BatchScript class
 *
 * @package    YapepBase
 * @subpackage Test\Batch
 */
class BatchScriptTest extends \YapepBase\BaseTest {

	/**
	 * The error handler instance.
	 *
	 * @var \YapepBase\Mock\ErrorHandler\ErrorHandlerMock
	 */
	protected $errorHandler;

	/**
	 * The event handler instance.
	 *
	 * @var \YapepBase\Mock\Event\EventHandlerMock
	 */
	protected $eventHandler;

	/**
	 * The orignal DI container
	 *
	 * @var \YapepBase\DependencyInjection\SystemContainer
	 */
	protected $originalDiContainer;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();
		$this->originalDiContainer = Application::getInstance()->getDiContainer();
		$diContainer = new SystemContainer();

		$this->errorHandler = new \YapepBase\Mock\ErrorHandler\ErrorHandlerMock();
		$this->eventHandler = new \YapepBase\Mock\Event\EventHandlerMock();

		$errorHandlerRegistry = new \YapepBase\ErrorHandler\ErrorHandlerRegistry();
		$errorHandlerRegistry->addErrorHandler($this->errorHandler);

		$eventHandlerRegistry = new \YapepBase\Event\EventHandlerRegistry();
		$eventHandlerRegistry->registerEventHandler(Event::TYPE_APPLICATION_BEFORE_RUN, $this->eventHandler);
		$eventHandlerRegistry->registerEventHandler(Event::TYPE_APPLICATION_AFTER_RUN, $this->eventHandler);

		$diContainer[SystemContainer::KEY_ERROR_HANDLER_REGISTRY] = $errorHandlerRegistry;
		$diContainer[SystemContainer::KEY_EVENT_HANDLER_REGISTRY] = $eventHandlerRegistry;

		Application::getInstance()->setDiContainer($diContainer);
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->setDiContainer($this->originalDiContainer);
	}

	/**
	 * Tests normal operation
	 *
	 * @return void
	 */
	public function testOperation() {
		$this->assertEmpty($this->eventHandler->handledEvents, 'The event handler contains events before the test');
		$this->assertEmpty($this->errorHandler->handledExceptions,
			'The error handler contains exceptions before the test');
		$this->assertEmpty($this->errorHandler->handledErrors, 'The error handler contains errors before the test');

		$eventHandler = $this->eventHandler;
		$script = new BatchScriptMock();
		$script->executeClosure = function() use ($script, $eventHandler) {
			$script->closureData = $eventHandler->handledEvents;
		};

		$script->run();

		$this->assertEquals(1, count($script->closureData),
			'Not 1 event has been triggered before the execute method is called');

		$this->assertEquals(2, count($this->eventHandler->handledEvents),
			'Not 2 events have been triggered after completing the run');

		$this->assertEmpty($this->errorHandler->handledExceptions,
			'The error handler contains exceptions after the test');
		$this->assertEmpty($this->errorHandler->handledErrors, 'The error handler contains errors after the test');
	}

	/**
	 * Tests exception handling
	 *
	 * @return void
	 */
	public function testExceptionHandling() {
		$script = new BatchScriptMock();
		$script->executeClosure = function() {
			throw new \YapepBase\Exception\Exception('test');
		};

		$script->run();

		$this->assertEquals(2, count($this->eventHandler->handledEvents),
			'Not 2 events have been triggered after completing the error run');

		$this->assertEquals(1, count($this->errorHandler->handledExceptions),
			'The error handler should contain 1 exception after the test');
		$this->assertEmpty($this->errorHandler->handledErrors, 'The error handler contains errors after the test');
	}

	/**
	 * Tests data storage in the view DO
	 *
	 * @return void
	 */
	public function testViewDoHandling() {
		$script = new BatchScriptMock();
		$script->setToView('test', 'testValue');

		$this->assertSame('testValue', Application::getInstance()->getDiContainer()->getViewDo()->get('test'),
			'The data stored in the view DO does not match.');
	}

}