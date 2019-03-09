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
use YapepBase\Config;
use YapepBase\Exception\ControllerException;
use YapepBase\View\RestTemplate;

/**
 * Base class for restful API controllers.
 *
 * Global configuration options:
 * <ul>
 *     <li>system.rest.xmlRootNode: The root node for XML output format.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class RestControllerAbstract extends HttpControllerAbstract {

	/**
	 * Returns the controller specific prefix
	 *
	 * @return string
	 */
	protected function getActionPrefix() {
		return strtolower($this->request->getMethod());
	}

	/**
	 * Runs the action and returns the result as an ViewAbstract instance.
	 *
	 * @param string $methodName   The name of the method that contains the action.
	 *
	 * @return string|\YapepBase\View\RestTemplate   The result view or the rendered output.
	 *
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	protected function runAction($methodName) {
		$result = $this->$methodName();
		if (is_array($result)) {
			$view = new RestTemplate();
			$view->setRootNode(Config::getInstance()->get('system.rest.xmlRootNode', 'rest'));
			$view->setContent($result);
			return $view;
		} elseif (!is_string($result) && !($result instanceof RestTemplate)) {
			throw new ControllerException('The received result is not a RestTemplate or an array or string',
				ControllerException::ERR_INVALID_ACTION_RETURN_VALUE);
		}
		return $result;
	}
}
