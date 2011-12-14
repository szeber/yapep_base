<?php

namespace YapepBase\Test\Mock\Autoloader;

class AutoloaderRegistryMock extends \YapepBase\Autoloader\AutoloaderRegistry {
    public $spl = false;

    public function registerWithSpl() {
        $this->spl = true;
    }

    public function unregisterFromSpl() {
        $this->spl = false;
    }
}