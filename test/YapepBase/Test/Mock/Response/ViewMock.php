<?php

namespace YapepBase\Test\Mock\Response;

use YapepBase\View\ViewAbstract;
use YapepBase\View\ViewDo;

/**
 * @codeCoverageIgnore
 */
class ViewMock extends ViewAbstract {
	protected $content = '';

	public function set($content = '') {
		$this->content = $content;
	}

	protected function renderContent() {
		echo $this->content;
	}

	public function render() {
		echo $this->content;
	}

	function __toString() {
		return $this->content;
	}

}