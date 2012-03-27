<?php

namespace YapepBase\Test\Mock\Controller;

/**
 * @codeCoverageIgnore
 */
class MockController extends \YapepBase\Controller\BaseController {
	public $ran = false;
	public function doTest() {
		$this->ran = true;
	}

	public function doError() {
		return 3.14;
	}

	public function doReturnString() {
		return 'test string';
	}

	public function doReturnView() {
		$view = new \YapepBase\Test\Mock\Response\ViewMock();
		$view->set('view test string');
		return $view;
	}

	public function doRedirect() {
		$this->internalRedirect('Mock', 'redirectTarget');
	}

	public function doRedirectTarget() {
		return 'redirect test';
	}
}