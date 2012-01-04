<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\Layout;

/**
 * Mock for a Layout.
 * @codeCoverageIgnore
 */
class MockLayout extends Layout {
    /**
     * Render the fake content
     */
    protected function renderContent() {
        echo 'Layout: ' . $this->getTemplateOutput();
    }
}