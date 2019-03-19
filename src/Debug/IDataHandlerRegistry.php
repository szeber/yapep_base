<?php
declare(strict_types=1);

namespace YapepBase\Debug;

use YapepBase\Exception\Exception;

interface IDataHandlerRegistry extends ICanStoreItems
{
    /**
     * Registers the given data handler under the given name
     *
     * @throws Exception
     */
    public function register(string $name, ICanReturnItems $dataHandler): void;

    /**
     * Unregisters the given handler
     */
    public function unregister($name): void;

    /**
     * Returns the handler with the given name
     */
    public function getHandler(string $name): ?ICanReturnItems;

    /**
     * Removes all handlers.
     */
    public function clear(): void;

    /**
     * Returns the time the registry was initiated
     */
    public function getInitiatedAt(): float;
}
