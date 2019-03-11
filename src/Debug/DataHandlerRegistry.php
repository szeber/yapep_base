<?php
declare(strict_types=1);

namespace YapepBase\Debug;

use YapepBase\Application;
use YapepBase\Debug\Item\CurlRequest;
use YapepBase\Debug\Item\Error;
use YapepBase\Debug\Item\Event;
use YapepBase\Debug\Item\MemoryUsage;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Debug\Item\Storage;
use YapepBase\Debug\Item\Time;
use YapepBase\Exception\Exception;
use YapepBase\Helper\DateHelper;

/**
 * Registry class for Debuggers
 */
class DataHandlerRegistry implements IDataHandlerRegistry
{
    /** @var float */
    protected $initiatedAt;

    /** @var ICanReturnItems[] */
    protected $dataHandlers = [];

    public function __construct()
    {
        /** @var DateHelper $dateHelper */
        $dateHelper = Application::getInstance()->getDiContainer()->get(DateHelper::class);

        $this->initiatedAt = $dateHelper->getCurrentTimestampMs();
    }

    public function register(string $name, ICanReturnItems $dataHandler): void
    {
        if (isset($this->dataHandlers[$name])) {
            throw new Exception('Data handler already registered with name: ' . $name);
        }

        $this->dataHandlers[$name] = $dataHandler;
    }

    public function unregister($name): void
    {
        unset($this->dataHandlers[$name]);
    }

    public function getHandler(string $name): ?ICanReturnItems
    {
        return isset($this->dataHandlers[$name])
            ? $this->dataHandlers[$name]
            : null;
    }

    public function clear(): void
    {
        $this->dataHandlers = [];
    }

    public function getInitiatedAt(): float
    {
        return $this->initiatedAt;
    }

    public function addCurlRequest(CurlRequest $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addCurlRequest($item);
        }
    }

    public function addError(Error $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addError($item);
        }
    }

    public function addEvent(Event $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addEvent($item);
        }
    }

    public function addMemoryUsage(MemoryUsage $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addMemoryUsage($item);
        }
    }

    public function addSqlQuery(SqlQuery $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addSqlQuery($item);
        }
    }

    public function addStorage(Storage $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addStorage($item);
        }
    }

    public function addTime(Time $item): void
    {
        foreach ($this->dataHandlers as $dataHandler) {
            $dataHandler->addTime($item);
        }
    }

}
