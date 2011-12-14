<?php

namespace YapepBase\Test\Mock\Autoloader;

class AutoloaderMock extends \YapepBase\Autoloader\AutoloaderBase {
    public $loaded = array();
    public $fail = false;

    public function load($className) {
        if ($this->fail) {
            return false;
        }
        $this->loaded[$className] = $className;
        return true;
    }
}