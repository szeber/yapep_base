<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Controller;

use YapepBase\Controller\DefaultErrorController;

/**
 * Mock class for testing error controllers
 *
 * @package    YapepBase
 * @subpackage Mock\Controller
 */
class ErrorControllerMock extends DefaultErrorController {

	/**
	 * The closure
	 *
	 * @var Closure
	 */
	public $actionClosure;

	/**
	 * The 404 action
	 *
	 * @return string|\YapepBase\View\TemplateAbstract
	 */
	protected function do404() {
		/** @var Closure $closure  */
		$closure = $this->actionClosure;
		return $closure($this);
	}

	/**
	 * The 500 action
	 *
	 * @return string|\YapepBase\View\TemplateAbstract
	 */
	protected function do500() {
		/** @var Closure $closure  */
		$closure = $this->actionClosure;
		return $closure($this);
	}

}