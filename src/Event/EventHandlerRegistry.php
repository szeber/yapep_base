<?php
declare(strict_types=1);

namespace YapepBase\Event;

/**
 * Registry class storing the registered event handlers.
 */
class EventHandlerRegistry implements IEventHandlerRegistry
{
    /**
     * @var array
     */
    protected $eventHandlersByType = [];

    /**
     * @var array
     */
    protected $lastRaisedByTypeInMs = [];

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
        $type                              = $event->getType();
        $this->lastRaisedByTypeInMs[$type] = microtime(true);

        if (!empty($this->eventHandlersByType[$type])) {
            /** @var IEventHandler $handler */
            foreach ($this->eventHandlersByType[$type] as $handler) {
                $handler->handleEvent($event);
            }
        }
    }

    public function getLastRaisedInMs(string $eventType): ?float
    {
        return isset($this->lastRaisedByTypeInMs[$eventType])
            ? $this->lastRaisedByTypeInMs[$eventType]
            : null;
    }
}
