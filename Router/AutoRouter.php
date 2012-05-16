<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Router;
use YapepBase\Request\IRequest;

/**
 * AutoRouter class.
 *
 * Generates the controller and action name based on the received target.
 *
 * @package    YapepBase
 * @subpackage Router
 */
class AutoRouter implements IRouter {

	/**
	 * The request instance
	 *
	 * @var \YapepBase\Request\IRequest
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \YapepBase\Request\IRequest $request   The request instance
	 */
	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	/**
	 * Returns a controller and an action for the request's target.
	 *
	 * @param string $controller   $he controller class name. (Outgoing parameter)
	 * @param string $action       The action name in the controller class. (Outgoing parameter)
	 *
	 * @return string   The controller and action separated by a '/' character.
	 *
	 * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
	 */
	public function getRoute(&$controller = null, &$action = null) {
		$target = explode('/', trim($this->request->getTarget(), '/ '));
		$controller = array_shift($target);
		if (empty($controller)) {
			$controller = 'Index';
		} else {
			$controller = $this->convertPathPartToName($controller);
		}
		if (empty($target)) {
			$action = 'Index';
		} else {
			$action = $this->convertPathPartToName(array_shift($target));
		}
		foreach ($target as $key => $value) {
			$this->request->setParam($key, $value);
		}
		return $controller . '/' . $action;

	}

	/**
	 * Converts a path part to a controller or action name.
	 *
	 * @param string $string   The path.
	 *
	 * @return string
	 */
	protected function convertPathPartToName($string) {
		$parts = preg_split('/[-_ A-Z]/', preg_replace('/[^-_a-zA-Z0-9]/', '', $string));
		foreach ($parts as $key => $value) {
			$parts[$key] = ucfirst($value);
		}
		return implode('', $parts);
	}

	/**
	 * Converts a controller or action name to a path part
	 *
	 * @param string $name   The name of the controller or action.
	 *
	 * @return string
	 */
	protected function convertNameToPathPart($name) {
		return strtolower(substr($name, 0, 1)) . substr($name, 1);
	}

	/**
	 * Returns the target (eg. URL) for the controller and action
	 *
	 * @param string $controller   The name of the controller
	 * @param string $action       The name of the action
	 * @param array  $params       Associative array with the route params, if they are required.
	 *
	 * @return string   The target.
	 *
	 * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
	 */
	public function getTargetForControllerAction($controller, $action, $params = array()) {
		if ('Index' == $action && 'Index' == $controller && empty($params)) {
			$path = '/';
		} elseif ('Index' == $action && empty($params)) {
			$path = '/' . $this->convertNameToPathPart($controller);
		} else {
			$path = '/' . $this->convertNameToPathPart($controller) . '/' . $this->convertNameToPathPart($action);
		}
		if (!empty($params)) {
			$path .= '/' . implode('/', $params);
		}
		return $path;
	}

}