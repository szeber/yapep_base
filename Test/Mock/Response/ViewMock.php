<?php

namespace YapepBase\Test\Mock\Response;
use YapepBase\View\IView;
use YapepBase\View\ViewDo;

/**
 * @codeCoverageIgnore
 */
class ViewMock implements IView {
	protected $content = '';

	public function set($content = '') {
		$this->content = $content;
	}

	public function render() {
		echo $this->content;
	}

	function __toString() {
		return $this->content;
	}

}