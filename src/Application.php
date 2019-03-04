<?php
declare(strict_types=1);

namespace YapepBase;

use YapepBase\Controller\IController;
use YapepBase\DependencyInjection\IContainer;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Exception\RedirectException;
use YapepBase\Event\Event;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\Router\IRouter;
use YapepBase\DependencyInjection\Container;
use YapepBase\Exception\Exception;
use YapepBase\Exception\HttpException;
use YapepBase\Exception\ControllerException;
use YapepBase\Exception\RouterException;
use YapepBase\ErrorHandler\ErrorHandlerRegistry;

/**
 * Singleton class responsible to "hold" the application together by managing the dispatch process.
 */
class Application
{
    /** @var static */
    protected static $instance;

    /** @var IRouter|null */
    protected $router;

    /** @var ErrorHandlerRegistry||null */
    protected $errorHandlerRegistry;

    /** @var Container||null */
    protected $diContainer;

    /** @var string */
    protected $errorControllerName;

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        $this->errorHandlerRegistry = $this->getDiContainer()->get($this->getDiContainer()->getErrorHandlerRegistryId());

        $this->errorHandlerRegistry->register();
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
    public static function getInstance(): Application
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
    public static function setInstance(Application $instance): void
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
            $this->diContainer = new Container();
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
        /** @var ErrorHandlerRegistry $errorHandlerRegistry */
        $errorHandlerRegistry = $this->diContainer->get($this->getDiContainer()->getErrorHandlerRegistryId());
        $eventHandlerRegistry = $this->getEventHandlerRegistry();

        $errorHandlerRegistry->reportApplicationRun();

        try {
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_RUN));

            $controllerName = '';
            $action         = '';

            try {
                $this->router->getRoute($controllerName, $action);
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
            $this->getResponse()->render();
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

    /**
     * Runs the given Action on the given Controller
     *
     * @throws ControllerException
     * @throws Exception
     * @throws RedirectException
     */
    protected function runAction(string $controllerName, string $action): void
    {
        $eventHandlerRegistry = $this->getEventHandlerRegistry();
        $controller           = $this->getController($controllerName);

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));

        $controller->run($action);

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN));
    }

    protected function getController(string $controllerName): IController
    {
        /** @var IController $controller */
        $controller = $this->diContainer->get($controllerName);

        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

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
        $eventHandlerRegistry = $this->getEventHandlerRegistry();

        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND));
        $this->getResponse()->send();
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND));
    }

    /**
     * @throws \Exception   Re-throws the received exception for the exception handler to handle.
     */
    protected function handleFatalException(\Exception $exception): void
    {
        $request = $this->getRequest();

        if ($request instanceof HttpRequest) {
            $this->errorHandlerRegistry->handleException($exception);
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
        $controller = $this->diContainer->get($this->errorControllerName);

        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN);
        $controller->run($errorCode);
        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN);

        $response = $this->getResponse();
        $response->render();

        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND);
        $response->send();
        $this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND);
    }

    /**
     * Raises an event if an event with it's type was not yet raised.
     *
     * @param string $eventType
     *
     * @return void
     */
    protected function raiseEventIfNotRaisedYet(string $eventType)
    {
        $eventHandlerRegistry = $this->getEventHandlerRegistry();

        if (is_null($eventHandlerRegistry->getLastTimeForEventType($eventType))) {
            $eventHandlerRegistry->raise(new Event($eventType));
        }
    }

    /**
     * Sends an error to the output.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function outputError()
    {
        try {
            $this->getResponse()->sendError();
        } catch (\Exception $exception) {
            error_log('Uncaught exception during error shutdown: ' . $exception->getMessage());
            exit;
        }
    }

    private function getResponse(): IResponse
    {
        return $this->diContainer->get($this->getDiContainer()->getResponseId());
    }

    private function getRequest(): IRequest
    {
        return $this->diContainer->get($this->getDiContainer()->getRequestId());
    }

    private function getEventHandlerRegistry(): EventHandlerRegistry
    {
        return  $this->diContainer->get($this->getDiContainer()->getEventHandlerRegistryId());
    }
}
