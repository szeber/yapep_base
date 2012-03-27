<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\BlockAbstract;

/**
 * Mock for a Block.
 * @codeCoverageIgnore
 */
class MockBlock extends BlockAbstract {
	/**
	 * Render the fake content
	 */
	protected function renderContent() {
		echo 'Block: ' . $this->getTemplateOutput();
	}
}
