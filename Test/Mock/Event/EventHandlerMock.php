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
	 * Stores the handled event
	 *
	 * @var \YapepBase\Event\Event
	 */
	protected $lastHandledEvent;

	/**
	 * Stores the number of event handlings.
	 *
	 * @var unknown_type
	 */
	protected $handleCounts = 0;

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Event.IEventHandler::handleEvent()
	 */
	public function handleEvent(Event $event) {
		$this->lastHandledEvent = $event;
		$this->handleCounts++;
	}

	/**
	 * Returns the last handled event
	 *
	 * @return \YapepBase\Event\Event
	 */
	public function getLastHandledEvent() {
		return $this->lastHandledEvent;
	}

	/**
	 * Returns the number of times the handleEvent method was called
	 *
	 * @return int
	 */
	public function getHandleCounts() {
		return $this->handleCounts;
	}
}