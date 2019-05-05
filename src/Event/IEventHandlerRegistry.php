<?php
declare(strict_types=1);

namespace YapepBase\Event;

/**
 * Registry class storing the registered event handlers.
 *
 * @see Event Type constants.
 */
interface IEventHandlerRegistry
{
    /**
     * Registers a new event handler for the given event type
     */
    public function add(string $eventType, IEventHandler $eventHandler): void;

    /**
     * Removes an event handler
     */
    public function remove(string $eventType, IEventHandler $eventHandler): void;

    /**
     * Returns an array containing all the event handlers registered to the event type
     *
     * @return IEventHandler[]
     */
    public function getEventHandlers(string $eventType): array;

    /**
     * Clears all event handlers for an event type
     */
    public function clear(string $eventType): void;

    /**
     * Clears all event handlers for all event types
     */
    public function clearAll(): void;

    /**
     * Raises an event
     */
    public function raise(Event $event): void;

    /**
     * Tells if the event has been raised already.
     */
    public function isRaised(string $event): bool;

    /**
     * Returns the timestamp of the time the given event type was last raised in Micro Seconds.
     *
     * @return float[]
     */
    public function getRaiseTimes(string $eventType): array;

    /**
     * Returns the last raise time of all raised events.
     *
     * @return float[][]
     */
    public function getAllRaiseTimes(): array;
}
