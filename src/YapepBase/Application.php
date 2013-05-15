<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package   YapepBase
 * @copyright 2011 The YAPEP Project All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
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
use YapepBase\I18n\ITranslator;

/**
 * Application singleton class.
 *
 * Global config options affecting this class:
 * <ul>
 *  <li>system.errorController: The name of the error controller for the application without the Controller suffix.
 *                              If not defined, defaults to Error.</li>
 * </ul>
 *
 * @package    YapepBase
 *
 * @todo debugger support
 */
class Application {

	/** The name of the default Error Controller. */
	const DEFAULT_ERROR_CONTROLLER_NAME = 'Error';

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
	 * Stores the i18n translator instance
	 *
	 * @var \YapepBase\I18n\ITranslator
	 */
	protected $i18nTranslator;

	/**
	 * Stores the name of the currently dispatched Controller.
	 *
	 * @var string
	 */
	protected $dispatchedController;

	/**
	 * Stores the name of the currently dispatched Action.
	 *
	 * @var string
	 */
	protected $dispatchedAction;

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
	 *
	 * @codeCoverageIgnore
	 */
	protected function __clone() {}

	/**
	 * Singleton getter
	 *
	 * @return \YapepBase\Application
	 *
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
	 * @param \YapepBase\Application $instance   The instance to use
	 *
	 * @return void
	 */
	public static function setInstance(Application $instance) {
		static::$instance = $instance;
	}

	/**
	 * Sets the router used by the application.
	 *
	 * @param \YapepBase\Router\IRouter $router   The router instance.
	 *
	 * @return void
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
	 * @param \YapepBase\DependencyInjection\SystemContainer $diContainer   The DI container instance to use
	 *
	 * @return void
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
	 * @param \YapepBase\Request\IRequest $request   The request instance.
	 *
	 * @return void
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
	 * @param \YapepBase\Response\IResponse $response   The response instance
	 *
	 * @return void
	 */
	public function setResponse(IResponse $response) {
		$this->response = $response;
	}

	/**
	 * Returns the configured i18n translator instance, or throws an exception if none is configured.
	 *
	 * @return \YapepBase\I18n\ITranslator   The instance.
	 *
	 * @throws Exception\Exception   If no translator is configured.
	 */
	public function getI18nTranslator() {
		if (empty($this->i18nTranslator)) {
			throw new Exception('No i18n translator is configured');
		}
		return $this->i18nTranslator;
	}

	/**
	 * Sets the i18n translator instance to use.
	 *
	 * @param \YapepBase\I18n\ITranslator $translator   The instance.
	 *
	 * @return void
	 */
	public function setI18nTranslator(ITranslator $translator) {
		$this->i18nTranslator = $translator;
	}

	/**
	 * Removes the configured translator instance.
	 *
	 * @return void
	 */
	public function clearI18nTranslator() {
		$this->i18nTranslator = null;
	}

	/**
	 * Returns the name of the currently dispatched Controller and Action.
	 *
	 * @param string $controllerName   The name of the Controller will be populated here (Outgoing parameter).
	 * @param string $actionName       The name of the Action will be populated here (Outgoing parameter).
	 *
	 * @return void
	 */
	public function getDispatchedAction(&$controllerName, &$actionName) {
		$controllerName = $this->dispatchedController;
		$actionName = $this->dispatchedAction;
	}

	/**
	 * Sets the name of the currently dispatched Controller and Action.
	 *
	 * @param string $controllerName   Name of the controller.
	 * @param string $actionName       Name of the action.
	 *
	 * @return void
	 */
	public function setDispatchedAction($controllerName, $actionName) {
		$this->dispatchedController = $controllerName;
		$this->dispatchedAction = $actionName;
	}

	/**
	 * Runs the request on the application
	 *
	 * @return void
	 */
	public function run() {
		$eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();
		try {
			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_RUN));
			$controllerName = null;
			$action = null;
			try {
				$this->router->getRoute($controllerName, $action);

				$this->dispatchedController = $controllerName;
				$this->dispatchedAction = $action;
			} catch (RouterException $exception) {
				if ($exception->getCode() == RouterException::ERR_NO_ROUTE_FOUND) {
					// The route was not found, generate a 404 HttpException
					throw new HttpException('Route not found. Controller/action: ' . $controllerName . '/' . $action,
						404);
				} else {
					// This was not a no route found error, re-throw the exception
					throw $exception;
				}
			}

			$controller = $this->getDiContainer()->getController($controllerName, $this->request, $this->response);

			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
			$controller->run($action);
			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN));

			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND));
			$this->response->send();
			$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND));
			// @codeCoverageIgnoreStart
		} catch (HttpException $exception) {
			$this->runErrorAction($exception->getCode());
		} catch (RedirectException $exception) {
			$this->response->send();
		} catch (\Exception $exception) {
			$this->handleFatalException($exception);
		}
		// Check that all required events were raised
		$requiredEventTypes = array(
			Event::TYPE_APPLICATION_BEFORE_RUN,
			Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN,
			Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN,
			Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND,
			Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND,
		);

		foreach ($requiredEventTypes as $eventType) {
			$this->raiseEventIfNotRaisedYet($eventType);
		}

		$eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_RUN));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Handles a fatal exception.
	 *
	 * @param \Exception $exception   The exception to handle.
	 *
	 * @return void
	 *
	 * @throws \Exception   Re-throws the received exception for the exception handler to handle.
	 */
	protected function handleFatalException(\Exception $exception) {
		if ($this->request instanceof \YapepBase\Request\HttpRequest) {
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
	 *
	 * @param int $errorCode   The error code
	 *
	 * @return void
	 */
	protected function runErrorAction($errorCode) {
		$controllerName = $this->config->get('system.errorController', self::DEFAULT_ERROR_CONTROLLER_NAME);

		try {
			try {
				$controller = $this->diContainer->getController($controllerName, $this->request, $this->response);
			} catch (ControllerException $exception) {
				// No such controller, fall back to built in default
				$controller = $this->diContainer->getDefaultErrorController($this->request, $this->response);
				$controllerName = self::DEFAULT_ERROR_CONTROLLER_NAME;
			}
		} catch (\Exception $e) {
			$this->dispatchedController = $controllerName;
			$this->dispatchedAction = 500;

			throw $e;
		}

		$this->dispatchedController = $controllerName;
		$this->dispatchedAction = $errorCode;

		$this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN);
		$controller->run($errorCode);
		$this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN);

		$this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_BEFORE_OUTPUT_SEND);
		$this->response->send();
		$this->raiseEventIfNotRaisedYet(Event::TYPE_APPLICATION_AFTER_OUTPUT_SEND);
	}

	/**
	 * Raises an event if an event with it's type was not yet raised.
	 *
	 * @param string $eventType   The event type
	 *
	 * @return void
	 */
	protected function raiseEventIfNotRaisedYet($eventType) {
		$eventHandlerRegistry = $this->diContainer->getEventHandlerRegistry();
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
	public function outputError() {
		try {
			$this->response->sendError();
		} catch (\Exception $exception) {
			error_log('Uncaught exception during error shutdown: ' . $exception->getMessage());
			exit;
		}
	}
}