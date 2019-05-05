<?php
declare(strict_types=1);

namespace YapepBase\Event;

/**
 * Registry class storing the registered event handlers.
 */
class EventHandlerRegistry implements IEventHandlerRegistry
{
    /** @var IEventHandler[][] */
    private $handlersByEvent = [];

    /** @var float[][] */
    private $raiseTimesInMsByEvent = [];

    public function add(string $event, IEventHandler $eventHandler): void
    {
        if (!isset($this->handlersByEvent[$event])) {
            $this->handlersByEvent[$event] = [];
        }

        $this->handlersByEvent[$event][] = $eventHandler;
    }

    public function remove(string $event, IEventHandler $eventHandler): void
    {
        $indexOfHandler = false;
        if (!empty($this->handlersByEvent[$event])) {
            $indexOfHandler = array_search($eventHandler, $this->handlersByEvent[$event], true);
        }

        if ($indexOfHandler !== false) {
            unset($this->handlersByEvent[$event][$indexOfHandler]);
        }
    }

    public function getEventHandlers(string $event): array
    {
        return isset($this->handlersByEvent[$event]) ? $this->handlersByEvent[$event] : [];
    }

    public function clear(string $event): void
    {
        $this->handlersByEvent[$event] = [];
    }

    public function clearAll(): void
    {
        $this->handlersByEvent = [];
    }

    public function raise(Event $event): void
    {
        $name                                 = $event->getName();
        $this->raiseTimesInMsByEvent[$name][] = microtime(true);

        if (!empty($this->handlersByEvent[$name])) {
            /** @var IEventHandler $handler */
            foreach ($this->handlersByEvent[$name] as $handler) {
                $handler->handleEvent($event);
            }
        }
    }

    public function isRaised(string $event): bool
    {
        return isset($this->raiseTimesInMsByEvent[$event]);
    }

    public function getRaiseTimes(string $event): array
    {
        return isset($this->raiseTimesInMsByEvent[$event])
            ? $this->raiseTimesInMsByEvent[$event]
            : [];
    }

    public function getAllRaiseTimes(): array
    {
        return $this->raiseTimesInMsByEvent;
    }
}
