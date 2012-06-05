<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Event
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Test\Mock\Event;
use YapepBase\Event\Event;
use YapepBase\Event\IEventHandler;

/**
 * EventHandlerMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Event
 * @codeCoverageIgnore
 */
class EventHandlerMock implements IEventHandler{

	/**
	 * Stores the handled events
	 *
	 * @var array
	 */
	public $handledEvents = array();

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Event.IEventHandler::handleEvent()
	 */
	public function handleEvent(Event $event) {
		$this->handledEvents[] = $event;
	}

	/**
	 * Returns the last handled event or FALSE if there are no handled events.
	 *
	 * @return \YapepBase\Event\Event|bool
	 */
	public function getLastHandledEvent() {
		return end($this->handledEvents);
	}

	/**
	 * Returns the number of times the handleEvent method was called
	 *
	 * @return int
	 */
	public function getHandleCounts() {
		return count($this->handledEvents);
	}
}