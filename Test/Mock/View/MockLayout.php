<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\LayoutAbstract;

/**
 * Mock for a Layout.
 * @codeCoverageIgnore
 */
class MockLayout extends LayoutAbstract {
	/**
	 * Render the fake content
	 */
	protected function renderContent() {
		echo 'Layout: ' . $this->getTemplateOutput();
	}
}
