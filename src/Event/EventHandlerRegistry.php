<?php
declare(strict_types=1);

namespace YapepBase\Event;

/**
 * Registry class storing the registered event handlers.
 */
class EventHandlerRegistry implements IEventHandlerRegistry
{
    /** @var array*/
    protected $eventHandlersByType = [];

    /** @var array */
    protected $lastRaisedByTypeInMs = [];

    /** @var array  */
    protected $raisedEvents = [];

    public function registerEventHandler(string $eventType, IEventHandler $eventHandler): void
    {
        if (!isset($this->eventHandlersByType[$eventType])) {
            $this->eventHandlersByType[$eventType] = [];
        }
        $this->eventHandlersByType[$eventType][] = $eventHandler;
    }

    public function removeEventHandler(string $eventType, IEventHandler $eventHandler): void
    {
        if (!empty($this->eventHandlersByType[$eventType]) && false !== ($key = array_search(
            $eventHandler,
            $this->eventHandlersByType[$eventType],
            true
        ))) {
            unset($this->eventHandlersByType[$eventType][$key]);
        }
    }

    public function getEventHandlers(string $eventType): array
    {
        return isset($this->eventHandlersByType[$eventType]) ? $this->eventHandlersByType[$eventType] : [];
    }

    public function clear(string $eventType): void
    {
        $this->eventHandlersByType[$eventType] = [];
    }

    public function clearAll(): void
    {
        $this->eventHandlersByType = [];
    }

    public function raise(Event $event): void
    {
        $eventName                              = $event->getType();
        $this->lastRaisedByTypeInMs[$eventName] = microtime(true);

        if (!empty($this->eventHandlersByType[$eventName])) {
            /** @var IEventHandler $handler */
            foreach ($this->eventHandlersByType[$eventName] as $handler) {
                $handler->handleEvent($event);
            }
        }
        $this->raisedEvents[] = $eventName;
    }

    public function getLastRaisedInMs(string $eventType): ?float
    {
        return isset($this->lastRaisedByTypeInMs[$eventType])
            ? $this->lastRaisedByTypeInMs[$eventType]
            : null;
    }

    public function isRaised(string $eventName): bool
    {
        return in_array($eventName, $this->raisedEvents);
    }
}
