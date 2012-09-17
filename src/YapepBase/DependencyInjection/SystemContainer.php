<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   DependencyInjection
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\DependencyInjection;


use YapepBase\Debugger\IDebugger;
use YapepBase\DependencyInjection\Container;
use YapepBase\ErrorHandler\ErrorHandlerRegistry;
use YapepBase\Event\Event;
use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Event\IEventHandler;
use YapepBase\Exception\ViewException;
use YapepBase\Exception\ControllerException;
use YapepBase\Exception\DiException;
use YapepBase\Exception\Exception;
use YapepBase\Log\LoggerRegistry;
use YapepBase\Log\Message\ErrorMessage;
use YapepBase\Mime\MimeType;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\Session\SessionRegistry;
use YapepBase\Storage\IStorage;
use YapepBase\View\ViewDo;

/**
 * Generic DI container implementation used in the framework.
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class SystemContainer extends Container {
	/** Error log message key. */
	const KEY_ERROR_LOG_MESSAGE = 'errorLogMessage';
	/** Error handler registry key. */
	const KEY_ERROR_HANDLER_REGISTRY = 'errorHandlerRegistry';
	/** Event handler container key. */
	const KEY_EVENT_HANDLER_REGISTRY = 'eventHandlerRegistry';
	/** Session registry key. */
	const KEY_SESSION_REGISTRY = 'sessionRegistry';
	/** Logger registry key. */
	const KEY_LOGGER_REGISTRY = 'loggerRegistry';
	/** Memcache key. */
	const KEY_MEMCACHE = 'memcache';
	/** Memcache key. */
	const KEY_MEMCACHED = 'memcached';
	/** Key containing the default error controller class' name. */
	const KEY_DEFAULT_ERROR_CONTROLLER_NAME = 'defaultErrorControllerName';
	/** Key containing the ViewDo. */
	const KEY_VIEW_DO = 'viewDo';

	/**
	 * Name of the namespace which holds the controllers.
	 *
	 * Responsible for handling the request, sanitize the input parameters
	 * and to collect the data needed to the response.
	 */
	const NAMESPACE_SEARCH_CONTROLLER = 'controller';

	/**
	 * Name of the namespace which holds the Business Objects.
	 *
	 * BO layer is responsible to communicate between the Controller and DAO.
	 * It should handle almost all kind of data manipulation, and data caching related to the application.
	 */
	const NAMESPACE_SEARCH_BO = 'bo';

	/**
	 * Name of the namespace which holds the Dao-s.
	 *
	 * Data Access Object, only responsible for reaching, modifying data (mostly Database)
	 */
	const NAMESPACE_SEARCH_DAO = 'dao';

	/**
	 * Name of the namespace which holds the Validators.
	 *
	 * Validator object, holds validator methods.
	 */
	const NAMESPACE_SEARCH_VALIDATOR = 'validator';

	/**
	 * Name of the namespace which holds the templates.
	 *
	 * Only the view related logic can be implemented here.
	 */
	const NAMESPACE_SEARCH_TEMPLATE = 'template';

	/**
	 * @var IStorage   The storage used for caching data between the database and the application.
	 */
	protected $storageMiddleware;

	/**
	 * DebugConsole object.
	 *
	 * @var \YapepBase\Debugger\IDebugger
	 */
	protected $debugger;

	/**
	 * List of namespaces to search in for each namespace search.
	 *
	 * @var array
	 */
	protected $searchNamespaces = array(
		self::NAMESPACE_SEARCH_TEMPLATE   => array(),
		self::NAMESPACE_SEARCH_CONTROLLER => array(),
		self::NAMESPACE_SEARCH_BO         => array(),
		self::NAMESPACE_SEARCH_DAO        => array(),
		self::NAMESPACE_SEARCH_VALIDATOR  => array(),
	);

	/**
	 * Constructor. Sets up the system DI objects.
	 */
	public function __construct() {
		$this[self::KEY_ERROR_LOG_MESSAGE] = function($container) {
			return new ErrorMessage();
		};
		$this[self::KEY_ERROR_HANDLER_REGISTRY] = $this->share(function($container) {
			return new ErrorHandlerRegistry();
		});
		$this[self::KEY_EVENT_HANDLER_REGISTRY] = $this->share(function($container) {
			return new EventHandlerRegistry();
		});
		$this[self::KEY_SESSION_REGISTRY] = $this->share(function($container) {
			return new SessionRegistry();
		});
		$this[self::KEY_LOGGER_REGISTRY] = $this->share(function($container) {
			return new LoggerRegistry();
		});
		if (class_exists('\Memcache')) {
			$this[self::KEY_MEMCACHE] = function($container) {
				return new \Memcache();
			};
		}
		if (class_exists('\Memcached')) {
			$this[self::KEY_MEMCACHED] = function($container) {
				return new \Memcached();
			};
		}
		$this[self::KEY_DEFAULT_ERROR_CONTROLLER_NAME] = '\\YapepBase\\Controller\\DefaultErrorController';

		$this[self::KEY_VIEW_DO] = $this->share(function($container) {
			return new ViewDo(MimeType::HTML);
		});

		$this->searchNamespaces[self::NAMESPACE_SEARCH_BO] = array();
		$this->searchNamespaces[self::NAMESPACE_SEARCH_DAO] = array();
		$this->searchNamespaces[self::NAMESPACE_SEARCH_VALIDATOR] = array();
	}

	/**
	 * Returns a logging ErrorMessage instance
	 *
	 * @return \YapepBase\Log\Message\ErrorMessage
	 */
	public function getErrorLogMessage() {
		return $this[self::KEY_ERROR_LOG_MESSAGE];
	}

	/**
	 * Returns an error handler registry instance
	 *
	 * @return \YapepBase\ErrorHandler\ErrorHandlerRegistry
	 */
	public function getErrorHandlerRegistry() {
		return $this[self::KEY_ERROR_HANDLER_REGISTRY];
	}

	/**
	 * Returns an event handler registry instance
	 *
	 * @return \YapepBase\Event\EventHandlerRegistry
	 */
	public function getEventHandlerRegistry() {
		return $this[self::KEY_EVENT_HANDLER_REGISTRY];
	}

	/**
	 * Returns a session registry instance
	 *
	 * @return \YapepBase\Session\SessionRegistry
	 */
	public function getSessionRegistry() {
		return $this[self::KEY_SESSION_REGISTRY];
	}

	/**
	 * Returns a logger registry instance.
	 *
	 * @return \YapepBase\Log\LoggerRegistry
	 */
	public function getLoggerRegistry() {
		return $this[self::KEY_LOGGER_REGISTRY];
	}

	/**
	 * Returns a memcache instance
	 *
	 * @return \Memcache
	 *
	 * @throws \YapepBase\Exception\Exception   If there is no Memcache support in PHP.
	 */
	public function getMemcache() {
		// @codeCoverageIgnoreStart
		if (!isset($this[self::KEY_MEMCACHE])) {
			throw new Exception('No memcache support in PHP');
		}
		// @codeCoverageIgnoreEnd
		return $this[self::KEY_MEMCACHE];
	}

	/**
	 * Returns a memcache instance
	 *
	 * @return \Memcached
	 *
	 * @throws \YapepBase\Exception\Exception   If there is no Memcached support in PHP.
	 */
	public function getMemcached() {
		// @codeCoverageIgnoreStart
		if (!isset($this[self::KEY_MEMCACHED])) {
			throw new Exception('No memcached support in PHP');
		}
		// @codeCoverageIgnoreEnd
		return $this[self::KEY_MEMCACHED];
	}

	/**
	 * Returns an instance of the default error controller.
	 *
	 * @param \YapepBase\Request\IRequest   $request    The request object.
	 * @param \YapepBase\Response\IResponse $response   The response object.
	 *
	 * @return \YapepBase\Controller\DefaultErrorController
	 */
	public function getDefaultErrorController(IRequest $request, IResponse $response) {
		return new $this[self::KEY_DEFAULT_ERROR_CONTROLLER_NAME]($request, $response);
	}

	/**
	 * Returns the ViewDo.
	 *
	 * @return \YapepBase\View\ViewDo
	 */
	public function getViewDo() {
		return $this[self::KEY_VIEW_DO];
	}

	/**
	 * Adds a namespace to the namespace roots for the given type, to search classes in.
	 *
	 * @param string $type        The type of the namespace. {@uses self::NAMESPACE_SEARCH_*}
	 * @param string $namespace   The namespace root to add.
	 *
	 * @return void
	 */
	public function addSearchNamespace($type, $namespace) {
		$this->searchNamespaces[$type][] = $namespace;
	}

	/**
	 * Sets a list of namespace roots to search the given type of classes in.
	 *
	 * @param string $type         The type of class to search for {@uses self::NAMESPACE_SEARCH_*}
	 * @param array  $namespaces   The list of namespaces
	 *
	 * @return void
	 */
	public function setSearchNamespaces($type, array $namespaces = array()) {
		$this->searchNamespaces[$type] = $namespaces;
	}

	/**
	 * Searches for the class in all the search namespaces for the given type
	 *
	 * @param string $type        The type of class to search for. {@uses self::NAMESPACE_SEARCH_*}
	 * @param string $className   The name of class to search for.
	 *
	 * @return string class name
	 *
	 * @throws \YapepBase\Exception\DiException           If the class is not found
	 */
	protected function searchForClass($type, $className) {
		if (isset($this->searchNamespaces[$type]) && is_array($this->searchNamespaces[$type])) {
			foreach ($this->searchNamespaces[$type] as $nsroot) {
				$fullName = $nsroot . '\\' . $className;
				if (\class_exists($fullName, true)) {
					return $fullName;
				}
			}
		}
		throw new DiException('Class ' . $className . ' not found.', DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND);

	}

	/**
	 * Searches for the controller in all the controller search namespaces
	 *
	 * @param string $controllerName   Name of the controller
	 *
	 * @return string controller name
	 *
	 * @throws \YapepBase\Exception\ControllerException   If the controller was not found
	 */
	protected function searchForController($controllerName) {
		try {
			return $this->searchForClass(self::NAMESPACE_SEARCH_CONTROLLER, $controllerName . 'Controller');
		} catch (DiException $e) {
			throw new ControllerException('Controller ' . $controllerName . ' not found in '
				. \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_CONTROLLER]),
				ControllerException::ERR_CONTROLLER_NOT_FOUND);
		}
	}

	/**
	 * Searches for the template in all the template search namespaces
	 *
	 * @param string $templateName   Name of the template
	 *
	 * @return string template name
	 *
	 * @throws \YapepBase\Exception\ViewException If the template was not found
	 */
	protected function searchForTemplate($templateName) {
		try {
			return $this->searchForClass(self::NAMESPACE_SEARCH_TEMPLATE, $templateName . 'Template');
		} catch (DiException $e) {
			throw new ViewException('Template ' . $templateName . ' not found in '
				. \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_TEMPLATE]),
				ViewException::ERR_TEMPLATE_NOT_FOUND);
		}
	}

	/**
	 * Returns a controller by it's name.
	 *
	 * @param string                        $controllerName   The name of the controller class to return.
	 *                                                        (Without the namespace and Controller suffix)
	 * @param \YapepBase\Request\IRequest   $request          The request object for the controller.
	 * @param \YapepBase\Response\IResponse $response         The response object for the controller.
	 *
	 * @return \YapepBase\Controller\IController
	 *
	 * @throws \YapepBase\Exception\ControllerException   If the controller was not found
	 */
	public function getController($controllerName, IRequest $request, IResponse $response) {
		$fullClassName = $this->searchForController($controllerName);
		return new $fullClassName($request, $response);
	}

	/**
	 * Returns a template by it's name
	 *
	 * @param string $templateName   The name of the template class to return.
	 *                               (Without the namespace and Template suffix)
	 *
	 * @return \YapepBase\View\TemplateAbstract
	 *
	 * @throws \YapepBase\Exception\ViewException If the block was not found
	 */
	public function getTemplate($templateName) {
		$fullClassName = $this->searchForTemplate($templateName);
		return new $fullClassName();
	}

	/**
	 * Stores the given debugger.
	 *
	 * @param \YapepBase\Debugger\IDebugger $debugger   The debugger object.
	 *
	 * @return void
	 */
	public function setDebugger(IDebugger $debugger) {
		$this->debugger = $debugger;
		if ($debugger instanceof IEventHandler) {
			$this->getEventHandlerRegistry()->registerEventHandler(Event::TYPE_APPFINISH, $debugger);
		}
	}

	/**
	 * Returns the Debugger.
	 *
	 * @return bool|\YapepBase\Debugger\IDebugger   The debugger object, or false if its not set.
	 */
	public function getDebugger() {
		if (empty($this->debugger)) {
			return false;
		}
		return $this->debugger;
	}

	/**
	 * Returns a BO by it's name
	 *
	 * @param string $name   The name of the BO class to return.
	 *                       (Without the namespace and Bo suffix)
	 *
	 * @return \YapepBase\BusinessObject\BoAbstract
	 *
	 * @throws \YapepBase\Exception\DiException   If the BO was not found
	 */
	public function getBo($name) {
		$fullClassName = $this->searchForClass(self::NAMESPACE_SEARCH_BO, $name . 'Bo');
		return new $fullClassName();
	}

	/**
	 * Returns a DAO by it's name
	 *
	 * @param string $name   The name of the DAO class to return.
	 *                       (Without the namespace and Dao suffix)
	 *
	 * @return \YapepBase\Dao\DaoAbstract
	 *
	 * @throws \YapepBase\Exception\DiException   If the DAO was not found
	 */
	public function getDao($name) {
		$fullClassName = $this->searchForClass(self::NAMESPACE_SEARCH_DAO, $name . 'Dao');
		return new $fullClassName();
	}

	/**
	 * Returns a Validator by it's name
	 *
	 * @param string $name   The name of the Validator class to return.
	 *                       (Without the namespace and Validator suffix)
	 *
	 * @return \YapepBase\Validator\ValidatorAbstract
	 *
	 * @throws \YapepBase\Exception\DiException   If the Validator was not found
	 */
	public function getValidator($name) {
		$fullClassName = $this->searchForClass(self::NAMESPACE_SEARCH_VALIDATOR, $name . 'Validator');
		return new $fullClassName();
	}

	/**
	 * Stores the given storage handler.
	 *
	 * @param IStorage $storage   The storage handler.
	 *
	 * @return void
	 */
	public function setMiddlewareStorage(IStorage $storage) {
		$this->storageMiddleware = $storage;
	}

	/**
	 * Returns the storage handler.
	 *
	 * @return bool|IStorage   The storage handler.
	 *
	 * @throws \YapepBase\Exception\DiException   If no middleware storage is set.
	 */
	public function getMiddlewareStorage() {
		if (empty($this->storageMiddleware)) {
			throw new DiException('Middleware storage instance is not set', DiException::ERR_INSTANCE_NOT_SET);
		}
		return $this->storageMiddleware;
	}
}