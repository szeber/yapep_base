<?php
declare(strict_types=1);

namespace YapepBase;

use YapepBase\Controller\IController;
use YapepBase\DependencyInjection\Container;
use YapepBase\DependencyInjection\IContainer;
use YapepBase\Event\Event;
use YapepBase\Exception\ControllerException;
use YapepBase\Exception\Exception;
use YapepBase\Exception\HttpException;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\RouterException;
use YapepBase\Request\HttpRequest;

/**
 * Singleton class responsible to "hold" the application together by managing the dispatch process.
 */
class Application
{
    /** @var static */
    protected static $instance;

    /** @var Container||null */
    protected $diContainer;

    /** @var string */
    protected $errorControllerName;

    /** @var bool */
    protected $isStarted = false;

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
    }

    /**
     * Singleton __clone() method
     */
    protected function __clone()
    {
    }

    /**
     * Singleton getter
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Sets the application instance.
     *
     * Be careful with this method, since it breaks the Singleton pattern.
     */
    public static function setInstance(self $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Sets the DI container to be used by the application
     */
    public function setDiContainer(IContainer $diContainer): void
    {
        $this->diContainer = $diContainer;
    }

    /**
     * Returns the DI container used by the application
     */
    public function getDiContainer(): IContainer
    {
        if (empty($this->diContainer)) {
            throw new Exception('You need to set a configured DI Container to be able to use the Application');
        }

        return $this->diContainer;
    }

    /**
     * Sets the name of the Error controller to use.
     */
    public function setErrorController(string $errorControllerName): void
    {
        $this->errorControllerName = $errorControllerName;
    }

    /**
     * Runs the request on the application
     */
    public function run(): void
    {
        if (empty($this->errorControllerName)) {
            throw new Exception('Please set the error controller first');
        }

        $eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();

        $this->isStarted = true;

        try {
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_RUN));

            $controllerName = '';
            $action         = '';

            try {
                $this->diContainer->getRouter()->getControllerActionByMethodAndPath($controllerName, $action);
            } catch (RouterException $exception) {
                if ($exception->getCode() == RouterException::ERR_NO_ROUTE_FOUND) {
                    // The route was not found, generate a 404 HttpException
                    throw new HttpException('Route not found. Controller/action: ' . $controllerName . '/' . $action, 404);
                } else {
                    // This was not a no route found error, re-throw the exception
                    throw $exception;
                }
            }

            $this->runAction($controllerName, $action);
            $this->sendResponse();
        } catch (HttpException $exception) {
            $this->runErrorAction($exception->getCode());
        } catch (RedirectException $exception) {
            $this->sendResponse();
        } catch (\Exception $exception) {
            $this->handleFatalException($exception);
        }

        $this->raiseRequiredEventsIfNotRaisedYet();

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_RUN));
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    /**
     * Runs the given Action on the given Controller
     *
     * @throws ControllerException
     * @throws Exception
     * @throws RedirectException
     */
    protected function runAction(string $controllerName, string $action): void
    {
        $eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();
        $controller           = $this->getController($controllerName);

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));

        $controller->run($action);

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN));
    }

    protected function getController(string $controllerName): IController
    {
        /** @var IController $controller */
        $controller = $this->diContainer->get($controllerName);

        $controller->setRequest($this->diContainer->getRequest());
        $controller->setResponse($this->diContainer->getResponse());

        return $controller;
    }

    protected function raiseRequiredEventsIfNotRaisedYet(): void
    {
        $requiredEventTypes = [
            Event::TYPE_APPLICATION_BEFORE_RUN,
            Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN,
            Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN,
            Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND,
            Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND,
        ];

        foreach ($requiredEventTypes as $eventType) {
            $this->raiseEventIfNotRaisedYet($eventType);
        }
    }

    protected function sendResponse()
    {
        $response             = $this->diContainer->getResponse();
        $eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();

        $response->render();

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND));
        $response->send();
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND));
    }

    /**
     * @throws \Exception   Re-throws the received exception for the exception handler to handle.
     */
    protected function handleFatalException(\Exception $exception): void
    {
        if ($this->diContainer->getRequest() instanceof HttpRequest) {
            $this->diContainer->getErrorHandlerRegistry()->handleException($exception);
            // We have an HTTP request, try to run
            try {
                $this->runErrorAction(500);
            } catch (\Exception $exception) {
                $this->outputError();
            }
        } else {
            // Not an HTTP request, just use default error output
            $this->outputError();
        }
    }

    /**
     * Runs the error controller action for the specified HTTP error code.
     */
    protected function runErrorAction(int $errorCode): void
    {
        /** @var IController $controller */
        $controller = $this->getController($this->errorControllerName);

        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN);
        $controller->run((string)$errorCode);
        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN);

        $response = $this->diContainer->getResponse();
        $response->render();

        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND);
        $response->send();
        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND);
    }

    /**
     * Raises an event if an event with it's type was not yet raised.
     */
    protected function raiseEventIfNotRaisedYet(string $eventType): void
    {
        $eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();

        if (is_null($eventHandlerRegistry->getLastRaisedInMs($eventType))) {
            $eventHandlerRegistry->raise(new Event($eventType));
        }
    }

    /**
     * Sends an error to the output.
     */
    public function outputError(): void
    {
        try {
            $this->diContainer->getResponse()->sendError();
        } catch (\Exception $exception) {
            error_log('Uncaught exception during error shutdown: ' . $exception->getMessage());
            exit;
        }
    }
}
