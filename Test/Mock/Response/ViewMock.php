<?php

namespace YapepBase\Test\Mock\Response;
use YapepBase\View\IView;

/**
 * @codeCoverageIgnore
 */
class ViewMock implements IView {
    protected $content = '';

    public function set($content = '') {
        $this->content = $content;
    }

    public function render($contentType, $return = true) {
        if ($return) {
            return $this->content;
        } else {
            echo $this->content;
        }
    }
}