<?php

namespace YapepBase\Test\Mock\Response;
use YapepBase\View\IView;
use YapepBase\View\ViewDo;

/**
 * @codeCoverageIgnore
 */
class ViewMock implements IView {
	protected $content = '';

	public $viewDo;

	public function set($content = '') {
		$this->content = $content;
	}

	public function render() {
		echo $this->content;
	}

	function __toString() {
		return $this->content;
	}

	/**
	 * Sets the view DO instance used by the view.
	 *
	 * @param \YapepBase\View\ViewDo $viewDo  The ViewDo instance to use.
	 */
	public function setViewDo(ViewDo $viewDo) {
		$this->viewDo = $viewDo;
	}

}