<?php

namespace YapepBase\Test\Mock\View;

use \YapepBase\View\Template;

class TemplateMock extends Template {
    protected $required = array('var1');
    protected $var1;
    protected $var2;
    
    protected function renderContent() {
        return '';
    }
}