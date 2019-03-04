<?php
declare(strict_types=1);

namespace YapepBase\DependencyInjection;

use Psr\Container\ContainerInterface;

interface IContainer extends ContainerInterface
{
    public function getRouterId(): string;

    public function getRequestId(): string;

    public function getResponseId(): string;

    public function getErrorLogMessageId(): string;

    public function getErrorHandlerRegistryId(): string;

    public function getEventHandlerRegistryId(): string;

    public function getSessionRegistryId(): string;

    public function getLoggerRegistryId(): string;

    public function getFileHandlerId(): string;

    public function getCommandExecutorId(): string;

    public function getHttpStatusId(): string;
}
