<?php
declare(strict_types = 1);

namespace YapepBase\DependencyInjection;

use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\ErrorHandler\IErrorHandlerRegistry;
use YapepBase\Event\IEventHandlerRegistry;
use YapepBase\File\IFileHandler;
use YapepBase\Log\ILoggerRegistry;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\Router\IRouter;
use YapepBase\Session\ISessionRegistry;
use YapepBase\Shell\ICommandExecutor;
use YapepBase\View\Data\Data;
use YapepBase\View\Data\ICanEscape;

/**
 * Generic DI container implementation used in the framework.
 */
class PimpleContainer extends \Pimple\Container implements IContainer
{
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function getRouter(): IRouter
    {
        return $this->get(IRouter::class);
    }

    public function getRequest(): IRequest
    {
        return $this->get(IRequest::class);
    }

    public function getResponse(): IResponse
    {
        return $this->get(IResponse::class);
    }

    public function getErrorHandlerRegistry(): IErrorHandlerRegistry
    {
        return $this->get(IErrorHandlerRegistry::class);
    }

    public function getEventHandlerRegistry(): IEventHandlerRegistry
    {
        return $this->get(IEventHandlerRegistry::class);
    }

    public function getDebugDataHandlerRegistry(): IDataHandlerRegistry
    {
        return $this->get(IDataHandlerRegistry::class);
    }

    public function getSessionRegistry(): ISessionRegistry
    {
        return $this->get(ISessionRegistry::class);
    }

    public function getLoggerRegistry(): ILoggerRegistry
    {
        return $this->get(ILoggerRegistry::class);
    }

    public function getFileHandler(): IFileHandler
    {
        return $this->get(IFileHandler::class);
    }

    public function getCommandExecutor(): ICommandExecutor
    {
        return $this->get(ICommandExecutor::class);
    }

    public function getViewData(): Data
    {
        return $this->get(Data::class);
    }

    public function setRouter(IRouter $router): self
    {
        return $this->setInstance(IRouter::class, $router);
    }

    public function setRouterAsFactory(string $routerClass): self
    {
        return $this->setFactory(IRouter::class, $routerClass);
    }

    public function setRequest(IRequest $request): self
    {
        return $this->setInstance(IRequest::class, $request);
    }

    public function setRequestAsFactory(string $requestClass): self
    {
        return $this->setFactory(IRequest::class, $requestClass);
    }

    public function setResponse(IResponse $response): self
    {
        return $this->setInstance(IResponse::class, $response);
    }

    public function setResponseAsFactory(string $responseClass): self
    {
        return $this->setFactory(IResponse::class, $responseClass);
    }

    public function setErrorHandlerRegistry(IErrorHandlerRegistry $errorHandlerRegistry): self
    {
        return $this->setInstance(IErrorHandlerRegistry::class, $errorHandlerRegistry);
    }

    public function setErrorHandlerRegistryAsFactory(string $errorHandlerRegistryClass): self
    {
        return $this->setFactory(IErrorHandlerRegistry::class, $errorHandlerRegistryClass);
    }

    public function setEventHandlerRegistry(IEventHandlerRegistry $eventHandlerRegistry): self
    {
        return $this->setInstance(IEventHandlerRegistry::class, $eventHandlerRegistry);
    }

    public function setEventHandlerRegistryAsFactory(string $eventHandlerRegistryClass): self
    {
        return $this->setFactory(IEventHandlerRegistry::class, $eventHandlerRegistryClass);
    }

    public function setDebugDataHandlerRegistry(IEventHandlerRegistry $debugDataHandlerRegistry): self
    {
        return $this->setInstance(IDataHandlerRegistry::class, $debugDataHandlerRegistry);
    }

    public function setDebugDataHandlerRegistryAsFactory(string $debugDataHandlerRegistry): self
    {
        return $this->setFactory(IDataHandlerRegistry::class, $debugDataHandlerRegistry);
    }

    public function setSessionRegistry(ISessionRegistry $sessionRegistry): self
    {
        return $this->setInstance(ISessionRegistry::class, $sessionRegistry);
    }

    public function setSessionRegistryAsFactory(string $sessionRegistryClass): self
    {
        return $this->setFactory(ISessionRegistry::class, $sessionRegistryClass);
    }

    public function setLoggerRegistry(ILoggerRegistry $loggerRegistry): self
    {
        return $this->setInstance(ILoggerRegistry::class, $loggerRegistry);
    }

    public function setLoggerRegistryAsFactory(string $loggerRegistryClass): self
    {
        return $this->setFactory(ILoggerRegistry::class, $loggerRegistryClass);
    }

    public function setFileHandler(IFileHandler $fileHandler): self
    {
        return $this->setInstance(IFileHandler::class, $fileHandler);
    }

    public function setFileHandlerAsFactory(string $fileHandlerClass): self
    {
        return $this->setFactory(IFileHandler::class, $fileHandlerClass);
    }

    public function setCommandExecutor(ICommandExecutor $commandExecutor): self
    {
        return $this->setInstance(ICommandExecutor::class, $commandExecutor);
    }

    public function setCommandExecutorAsFactory(string $commandExecutorClass): self
    {
        return $this->setFactory(ICommandExecutor::class, $commandExecutorClass);
    }

    public function setViewData(ICanEscape $data): self
    {
        return $this->setInstance(Data::class, $data);
    }

    protected function setInstance(string $id, $object): self
    {
        $this[$id] = function ($container) use ($object) {
            return $object;
        };

        return $this;
    }

    protected function setFactory(string $id, string $class): self
    {
        $this[$id] = $this->factory(function ($container) use ($class) {
            return new $class();
        });

        return $this;
    }
}
