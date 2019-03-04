<?php
declare(strict_types = 1);

namespace YapepBase\DependencyInjection;

use YapepBase\ErrorHandler\ErrorHandlerRegistry;

/**
 * Generic DI container implementation used in the framework.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class Container extends \Pimple\Container implements IContainer
{
    const FRAMEWORK_ID_PREFIX = 'yapepBase_';

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this[$this->getErrorHandlerRegistryId()] = function () {
            return new ErrorHandlerRegistry();
        };
    }


    public function getRouterId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'router';
    }

    public function getRequestId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'request';
    }

    public function getResponseId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'response';
    }

    public function getErrorLogMessageId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'error_log_message';
    }

    public function getErrorHandlerRegistryId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'error_handler_registry';
    }

    public function getEventHandlerRegistryId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'event_handler_registry';
    }

    public function getSessionRegistryId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'session_registry';
    }

    public function getLoggerRegistryId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'logger_registry';
    }

    public function getFileHandlerId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'file_handler';
    }

    public function getCommandExecutorId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'command_executor';
    }

    public function getHttpStatusId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'http_status';
    }

    public function getDebuggerId(): string
    {
        return self::FRAMEWORK_ID_PREFIX . 'debugger';
    }

    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }
}
