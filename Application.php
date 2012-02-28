<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase;
use YapepBase\Exception\RedirectException;
use YapepBase\Event\Event;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\ErrorHandler\IErrorHandler;
use YapepBase\Router\IRouter;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Debugger\IDebugger;
use YapepBase\Exception\Exception;
use YapepBase\Exception\HttpException;
use YapepBase\ErrorHandler\ErrorHandlerRegistry;
use YapepBase\Request\HttpRequest;
use YapepBase\Exception\ControllerException;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Exception\RouterException;

/**
 * Application singleton class
 *
 * @package    YapepBase
 *
 * @tood debugger support
 */
class Application {

    /**
     * Singleton instance
     *
     * @var \YapepBase\Application
     */
    protected static $instance;

    /**
     * The router instance
     *
     * @var \YapepBase\Router\IRouter
     */
    protected $router;

    /**
     * The request instance for the application.
     *
     * @var \YapepBase\Request\IRequest
     */
    protected $request;

    /**
     * The response instance for the application.
     *
     * @var \YapepBase\Response\IResponse
     */
    protected $response;

    /**
     * The configuration instance
     *
     * @var \YapepBase\Config
     */
    protected $config;

    /**
     * The error handler container instance
     *
     * @var \YapepBase\ErrorHandler\ErrorHandlerRegistry
     */
    protected $errorHandlerRegistry;

    /**
     * Stores the system DI container
     *
     * @var \YapepBase\DependencyInjection\SystemContainer
     */
    protected $diContainer;

    /**
     * Singleton constructor
     */
    protected function __construct() {
        $this->config = Config::getInstance();
        // Set up error handling
        $this->errorHandlerRegistry = $this->getDiContainer()->getErrorHandlerRegistry();
        $this->errorHandlerRegistry->register();
    }

    /**
     * Singleton __clone() method
     * @codeCoverageIgnore
     */
    protected function __clone() {}

    /**
     * Singleton getter
     *
     * @return \YapepBase\Application
     * @codeCoverageIgnore
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Sets the application instance.
     *
     * Be careful with this method, since it breaks the Singleton pattern.
     *
     * @param \YapepBase\Application $instance
     */
    public static function setInstance(Application $instance) {
        static::$instance = $instance;
    }

    /**
     * Sets the router used by the application.
     *
     * @param \YapepBase\Router\IRouter $router
     */
    public function setRouter(IRouter $router) {
        $this->router = $router;
    }

    /**
     * Returns the router used by the application.
     *
     * @return \YapepBase\Router\IRouter
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * Sets the DI contianer to be used by the application
     *
     * @param \YapepBase\DependencyInjection\SystemContainer $diContainer
     */
    public function setDiContainer(SystemContainer $diContainer) {
        $this->diContainer = $diContainer;
    }

    /**
     * Returns the DI container used by the application
     *
     * @return \YapepBase\DependencyInjection\SystemContainer
     */
    public function getDiContainer() {
        if (empty($this->diContainer)) {
            $this->diContainer = new SystemContainer();
        }
        return $this->diContainer;
    }

	/**
	 * Returns the request object used by the application.
	 *
     * @return \YapepBase\Request\IRequest
     */
    public function getRequest() {
        return $this->request;
    }

	/**
	 * Sets the request object used by the application.
	 *
     * @param \YapepBase\Request\IRequest $request
     */
    public function setRequest(IRequest $request) {
        $this->request = $request;
    }

	/**
	 * Returns the response object used by the application.
	 *
     * @return \YapepBase\Response\IResponse $response
     */
    public function getResponse() {
        return $this->response;
    }

	/**
	 * Sets the response object used by the application.
	 *
     * @param \YapepBase\Response\IResponse $response
     */
    public function setResponse(IResponse $response) {
        $this->response = $response;
    }

    /**
     * Returns the errorhandler container instance
     *
     * @return \YapepBase\ErrorHandler\ErrorHandlerRegistry
     */
    public function getErrorHandlerRegistry() {
        return $this->errorHandlerRegistry;
    }

    /**
     * Runs the request on the application
     */
    public function run() {
        $eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();
        try {
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPSTART));
            $controllerName = null;
            $action = null;
            $this->router->getRoute($controllerName, $action);
            $controller = $this->getDiContainer()->getController($controllerName, $this->request, $this->response);
            $controller->run($action);
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
            $this->response->send();
            // @codeCoverageIgnoreStart
        } catch (RouterException $exception) {
            if ($exception->getCode() == RouterException::ERR_NO_ROUTE_FOUND) {
                $this->runErrorAction(404);
            } else {
                $this->handleFatalException($exception);
            }
        } catch (HttpException $exception) {
            $this->runErrorAction($exception->getCode());
        } catch (RedirectException $exception) {
            $eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
            $this->response->send();
        } catch (\Exception $exception) {
            $this->handleFatalException($exception);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Handles a fatal exception.
     *
     * @param \Exception $exception
     */
    protected function handleFatalException(\Exception $exception) {
        if ($this->request instanceof \YapepBase\Request\HttpRequest) {
            trigger_error('Unhandled exception of type: ' . get_class($exception), E_USER_ERROR);
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
     *
     * @param int $errorCode
     */
    protected function runErrorAction($errorCode) {
        $controllerName = Config::getInstance()->get('system.errorController', 'Error');
        try {
            $controller = $this->diContainer->getController($controllerName, $this->request, $this->response);
        } catch (ControllerException $exception) {
            // No such controller, fall back to built in default
            $controller = $this->diContainer->getDefaultErrorController($this->request, $this->response);
        }
        $controller->run($errorCode);
        $this->response->send();
    }

    /**
     * Sends an error to the output.
     * @codeCoverageIgnore
     */
    public function outputError() {
        try {
            $this->response->sendError();
        } catch (\Exception $exception) {
            error_log('Uncaught exception during error shutdown: ' . $exception->getMessage());
            exit;
        }
    }
}