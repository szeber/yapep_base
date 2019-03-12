<?php
declare(strict_types = 1);

namespace YapepBase\DependencyInjection;

use Psr\Container\ContainerInterface;
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

interface IContainer extends ContainerInterface
{
    public function getRouter(): IRouter;

    public function getRequest(): IRequest;

    public function getResponse(): IResponse;

    public function getErrorHandlerRegistry(): IErrorHandlerRegistry;

    public function getEventHandlerRegistry(): IEventHandlerRegistry;

    public function getDebugDataHandlerRegistry(): IDataHandlerRegistry;

    public function getSessionRegistry(): ISessionRegistry;

    public function getLoggerRegistry(): ILoggerRegistry;

    public function getFileHandler(): IFileHandler;

    public function getCommandExecutor(): ICommandExecutor;

    public function getViewData(): Data;
}
