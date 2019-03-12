<?php
declare(strict_types = 1);

namespace YapepBase\DependencyInjection;

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

/**
 * Generic DI container implementation used in the framework.
 */
class Container implements IContainer
{
    /** @var IContainer */
    protected $container;

    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function getRouter(): IRouter
    {
        return $this->container->getRouter();
    }

    public function getRequest(): IRequest
    {
        return $this->container->getRequest();
    }

    public function getResponse(): IResponse
    {
        return $this->container->getResponse();
    }

    public function getErrorHandlerRegistry(): IErrorHandlerRegistry
    {
        return $this->container->getErrorHandlerRegistry();
    }

    public function getEventHandlerRegistry(): IEventHandlerRegistry
    {
        return $this->container->getEventHandlerRegistry();
    }

    public function getSessionRegistry(): ISessionRegistry
    {
        return $this->container->getSessionRegistry();
    }

    public function getLoggerRegistry(): ILoggerRegistry
    {
        return $this->container->getLoggerRegistry();
    }

    public function getFileHandler(): IFileHandler
    {
        return $this->container->getFileHandler();
    }

    public function getCommandExecutor(): ICommandExecutor
    {
        return $this->container->getCommandExecutor();
    }

    public function getViewData(): Data
    {
        return $this->container->getViewData();
    }
}
