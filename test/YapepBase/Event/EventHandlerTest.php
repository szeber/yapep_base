<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Event
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Event;
use YapepBase\Mock\Event\EventHandlerMock;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Event\Event;

/**
 * EventHandlerTest class
 *
 * @package    YapepBase
 * @subpackage Test\Event
 */
class EventHandlerTest extends \PHPUnit_Framework_TestCase {

	public function testRegistration() {
		$registry = new EventHandlerRegistry();
		$eventHandler = new EventHandlerMock();

		$result = $registry->getEventHandlers('test');
		$this->assertTrue(empty($result), 'The test event handlers are not empty after instantiation.');

		$registry->registerEventHandler('test', $eventHandler);
		$result = $registry->getEventHandlers('test');
		$this->assertFalse(empty($result), 'The test event handlers are empty after registering.');
		$this->assertEquals(1, count($result), 'There is not 1 registered event handler.');

		$firstHandler = reset($result);
		$this->assertSame($eventHandler, $firstHandler, 'The event handler is not the same as what was set');

		$registry->removeEventHandler('test', $eventHandler);
		$result = $registry->getEventHandlers('test');
		$this->assertTrue(empty($result), 'Removing an event handler fails.');

		$registry->registerEventHandler('test', $eventHandler);
		$registry->clear('test');
		$result = $registry->getEventHandlers('test');
		$this->assertTrue(empty($result), 'Clearing the test event handlers fails.');

		$registry->registerEventHandler('test', $eventHandler);
		$registry->clearAll();
		$result = $registry->getEventHandlers('test');
		$this->assertTrue(empty($result), 'Clearing all event handlers does not clear the test handlers.');
	}

	public function testRaise() {
		$registry = new EventHandlerRegistry();
		$eventHandler = new EventHandlerMock();
		$testEvent = new Event('test', array('test' => 'test'));

		$registry->registerEventHandler('test', $eventHandler);

		$registry->raise($testEvent);

		$this->assertEquals(1, $eventHandler->getHandleCounts(), 'The event handler was not called once.');
		$this->assertSame($testEvent, $eventHandler->getLastHandledEvent(),
			'The handled event is not the same as was raised.');
	}
}