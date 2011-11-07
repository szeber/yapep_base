<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version      $Rev$
 */


namespace YapepBase;
use YapepBase\Debugger\IDebugger;
use YapepBase\Exception\Exception;

/**
 * Application singleton class
 *
 * @package    YapepBase
 */
class Application {

    protected static $instance;

    protected $router;

    protected $errorHandlers = array();

    protected $debuggers = array();

    protected function __construct() {
    }

    protected function __clone() {}

    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function setRouter(YapepBase\Router\IRouter $router) {
        $this->router = $router;
    }

    public function addErrorHandler(YapepBase\ErrorHandler\IErrorHandler $errorHandler) {
        $this->errorHandlers[] = $errorHandler;
    }

    public function removeErrorHandler(YapepBase\ErrorHandler\IErrorHandler $errorHandler) {
        $index = array_search($errorHandler, $this->errorHandlers);
        if (false === $index) {
            return false;
        }
        unset($this->errorHandlers[$index]);
    }

    public function getErrorHandlers() {
        return $this->errorHandlers;
    }

    public function addDebugger(IDebugger $debugger) {
        $this->debuggers[] = $debugger;
    }

    public function run() {

    }

    public function outputError() {

    }
}