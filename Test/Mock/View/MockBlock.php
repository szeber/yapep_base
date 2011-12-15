<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\Block;

/**
 * Mock for a Block.
 * @codeCoverageIgnore
 */
class MockBlock extends Block {
    /**
     * Render the fake content
     */
    protected function renderContent() {
        echo 'Block: ' . $this->getTemplateOutput();
    }
}