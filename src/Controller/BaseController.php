<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;


use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Exception\RedirectException;
use YapepBase\Exception\ControllerException;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\View\ViewAbstract;

/**
 * Base class for generic controllers.
 *
 * Configuration options:
 * <ul>
 *   <li>system.performStrictControllerActionNameValidation: If this option is TRUE, the action's name will be
 *                                                           validated in a case sensitive manner. This is recommended
 *                                                           for development, but not recommended for production as it
 *                                                           can cause errors, and will somewhat impact the performance.
 *                                                           Optional, defaults to FALSE.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class BaseController implements IController {

	/**
	 * The request instance
	 *
	 * @var \YapepBase\Request\IRequest;
	 */
	protected $request;

	/**
	 * The response instance
	 *
	 * @var \YapepBase\Response\IResponse
	 */
	protected $response;

	/**
	 * Constructor.
	 *
	 * @param \YapepBase\Request\HttpRequest|\YapepBase\Request\IRequest     $request    The request object.
	 * @param \YapepBase\Response\HttpResponse|\YapepBase\Response\IResponse $response   The response object.
	 *
	 * @throws \YapepBase\Exception\ControllerException   On error. (eg. incompatible request or response object)
	 */
	public function __construct(IRequest $request, IResponse $response) {
		$this->request = $request;
		$this->response = $response;
		Application::getInstance()->getDiContainer()->getViewDo()->clear();
	}

	/**
	 * Runs before the action.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On error.
	 */
	protected function before() {
		// Empty default implementation. Should be implemented by descendant classes if needed
	}

	/**
	 * Runs after the action
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On error.
	 */
	protected function after() {
		// Empty default implementation. Should be implemented by descendant classes if needed
	}

	/**
	 * Runs after the action but before the rendering.
	 *
	 * Can be useful to set collected data to the View.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On error.
	 */
	protected function runBeforeRender() {
		// Empty default implementation. Should be implemented by descendant classes if needed
	}

	/**
	 * Runs before the response is being set.
	 *
	 * Allows to modify the result of the action before it's set to response
	 *
	 * @param ViewAbstract|string $actionResult   The result of the called action
	 *
	 * @return void
	 */
	protected function runBeforeResponseSet(&$actionResult) {
		// Empty default implementation. Should be implemented by descendant classes if needed
	}

	/**
	 * Returns the controller specific prefix
	 *
	 * @return string
	 */
	protected function getActionPrefix() {
		return 'do';
	}

	/**
	 * Runs the specified action
	 *
	 * @param string $action   The name of the action (without the controller specific prefix)
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \YapepBase\Exception\RedirectException     On redirections.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	public function run($action) {
		$methodName = $this->getActionPrefix() . $action;
		if (!method_exists($this, $methodName)) {
			throw new ControllerException('Action ' . $methodName . ' does not exist in ' . get_class($this),
				ControllerException::ERR_ACTION_NOT_FOUND);
		}
		if (Config::getInstance()->get('system.performStrictControllerActionNameValidation', false)) {
			$reflection = new \ReflectionClass($this);
			$method = $reflection->getMethod($methodName);
			if ($method->name != $methodName) {
				throw new ControllerException('Invalid case when running action ' . $methodName . ' in '
					. get_class($this)  . '. The valid case is: ' . $method->name,
					ControllerException::ERR_ACTION_NOT_FOUND);
			}
		}
		$this->before();
		$result = $this->runAction($methodName);
		if (!empty($result) && !is_string($result) && !($result instanceof ViewAbstract)) {
			throw new ControllerException('Result of the action (' . get_class($this) . '/' . $action
					.  ') is not an instance of ViewAbstract or string',
				ControllerException::ERR_INVALID_ACTION_RESULT);
		}

		// We called the run method, but we did not rendered the output yet
		$this->runBeforeRender();

		$this->runBeforeResponseSet($result);

		if (!empty($result)) {
			if (is_string($result)) {
				$this->response->setRenderedBody($result);
			} else {
				$this->response->setBody($result);
			}
		}
		$this->after();
	}

	/**
	 * Runs the action and returns the result as an ViewAbstract instance
	 *
	 * @param string $methodName   Name of the method
	 *
	 * @return ViewAbstract
	 *
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	protected function runAction($methodName) {
		return $this->$methodName();
	}

	/**
	 * Does an internal redirect (forwards the call to another controller and action).
	 *
	 * Be careful to set the route params in the request before calling this method, if the target action uses any.
	 * The called action will use the same request and response objects.
	 *
	 * @param string $controllerName   The name of the controller.
	 * @param string $action           The name of the action.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\RedirectException     To stop execution of the controller.
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	protected function internalRedirect($controllerName, $action) {
		Application::getInstance()->setDispatchedAction($controllerName, $action);
		$controller = Application::getInstance()->getDiContainer()->getController($controllerName, $this->request,
			$this->response);
		$controller->run($action);
		throw new RedirectException($controllerName . '/' . $action, RedirectException::TYPE_INTERNAL);
	}

	/**
	 * Stores one ore more value(s).
	 *
	 * @param string $nameOrData   The name of the key, or the storable data in an associative array.
	 * @param mixed  $value        The value.
	 *
	 * @return void
	 *
	 * @throws \Exception   If the key already exists.
	 */
	protected function setToView($nameOrData, $value = null) {
		Application::getInstance()->getDiContainer()->getViewDo()->set($nameOrData, $value);
	}

	/**
	 * Translates the specified string.
	 *
	 * @param string $string       The string.
	 * @param array  $parameters   The parameters for the translation.
	 * @param string $language     The language.
	 *
	 * @return string
	 */
	protected function _($string, $parameters = array(), $language = null) {
		return Application::getInstance()->getI18nTranslator()->translate(get_class($this), $string, $parameters,
			$language);
	}

}
