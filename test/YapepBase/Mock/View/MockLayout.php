<?php

namespace YapepBase\Mock\View;


/**
 * Mock for a Layout.
 * @codeCoverageIgnore
 */
class MockLayout extends \YapepBase\View\LayoutAbstract {

	/**
	 * Render the fake content
	 */
	protected function renderContent() {
		echo 'Layout: ' . $this->renderInnerContent();
	}

	/**
	 * Displays the given block
	 *
	 * @param \YapepBase\View\BlockAbstract $block   The block.
	 *
	 * @return void
	 */
	public function renderBlock(\YapepBase\View\BlockAbstract $block) {
		parent::renderBlock($block);
	}
}
