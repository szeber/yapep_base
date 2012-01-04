<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\Template;

/**
 * @codeCoverageIgnore
 */
class MockTemplate extends Template {
    protected $required = array('var1');
    protected $var1;
    protected $var2;

    protected function renderContent() {
        echo 'test output';
    }
}