<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Event
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Event;
use YapepBase\Exception\Exception;

/**
 * Registry class storing the registered event handlers.
 *
 * @package    YapepBase
 * @subpackage Event
 */
class EventHandlerRegistry {

	/**
	 * Stores the event handler instances
	 *
	 * @var array
	 */
	protected $eventHandlers = array();

	/**
	 * Registers a new event handler for the given event type
	 *
	 * @param string                         $eventType      The event type. {@uses Event::TYPE_*}
	 * @param \YapepBase\Event\IEventHandler $eventHandler   The event handler.
	 *
	 * @return void
	 */
	public function registerEventHandler($eventType, IEventHandler $eventHandler) {
		if (!isset($this->eventHandlers[$eventType])) {
			$this->eventHandlers[$eventType] = array();
		}
		$this->eventHandlers[$eventType][] = $eventHandler;
	}

	/**
	 * Removes an event handler
	 *
	 * @param string                         $eventType      The event type. {@uses Event::TYPE_*}
	 * @param \YapepBase\Event\IEventHandler $eventHandler   The event handler.
	 *
	 * @return void
	 */
	public function removeEventHandler($eventType, IEventHandler $eventHandler) {
		if (
			!empty($this->eventHandlers[$eventType])
			&& false !== ($key = array_search($eventHandler, $this->eventHandlers[$eventType], true))
		) {
			unset($this->eventHandlers[$eventType][$key]);
		}
	}

	/**
	 * Returns an array containing all the event handlers registered to the event type
	 *
	 * @param string $eventType   the event type. {@uses Event::TYPE_*}
	 *
	 * @return array
	 */
	public function getEventHandlers($eventType) {
		return (isset($this->eventHandlers[$eventType]) ? $this->eventHandlers[$eventType] : array());
	}

	/**
	 * Clears all event handlers for an event type
	 *
	 * @param string $eventType   The event type. {@uses Event::TYPE_*}
	 *
	 * @return void
	 */
	public function clear($eventType) {
		$this->eventHandlers[$eventType] = array();
	}

	/**
	 * Clears all event handlers for all event types
	 *
	 * @return void
	 */
	public function clearAll() {
		$this->eventHandlers = array();
	}

	/**
	 * Raises an event
	 *
	 * @param Event $event   The event to raise.
	 *
	 * @return void
	 */
	public function raise(Event $event) {
		$type = $event->getType();
		if (!empty($this->eventHandlers[$type])) {
			foreach ($this->eventHandlers[$type] as $handler) {
				/** @var \YapepBase\Event\IEventHandler $handler */
				$handler->handleEvent($event);
			}
		}
	}
}