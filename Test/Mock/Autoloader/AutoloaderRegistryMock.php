<?php

namespace YapepBase\Test\Mock\Autoloader;

/**
 * @codeCoverageIgnore
 */
class AutoloaderRegistryMock extends \YapepBase\Autoloader\AutoloaderRegistry {
    public $spl = false;

    public function registerWithSpl() {
        $this->spl = true;
    }

    public function unregisterFromSpl() {
        $this->spl = false;
    }
}