<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase;
use YapepBase\ErrorHandler\IErrorHandler;
use YapepBase\Router\IRouter;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Debugger\IDebugger;
use YapepBase\Exception\Exception;
use YapepBase\ErrorHandler\ErrorHandlerContainer;

/**
 * Application singleton class
 *
 * @package    YapepBase
 */
class Application {

    /**
     * Singleton instance
     *
     * @var \YapepBase\Application
     */
    protected static $instance;

    /**
     * The router instance
     *
     * @var \YapepBase\Router\IRouter
     */
    protected $router;

    /**
     * Array containing the assigned debuggers.
     *
     * @var array
     */
    protected $debuggers = array();

    /**
     * The configuration instance
     *
     * @var \YapepBase\Config
     */
    protected $config;

    /**
     * The error handler container instance
     *
     * @var \YapepBase\ErrorHandler\ErrorHandlerContainer
     */
    protected $errorHandlerContainer;

    /**
     * Singleton constructor
     */
    protected function __construct() {
        $this->config = Config::getInstance();
        // Set up error handling
        $this->errorHandlerContainer = SystemContainer::getInstance()->getErrorHandlerContainer();
        $this->errorHandlerContainer->register();
    }

    /**
     * Singleton __clone() method
     */
    protected function __clone() {}

    /**
     * Singleton getter
     *
     * @return \YapepBase\Application
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Sets the router used by the application
     *
     * @param IRouter $router
     */
    public function setRouter(IRouter $router) {
        $this->router = $router;
    }

    /**
     * Returns the errorhandler container instance
     *
     * @return \YapepBase\ErrorHandler\ErrorHandlerContainer
     */
    public function getErrorHandlerContainer() {
        return $this->errorHandlerContainer;
    }

    /**
     * Adds a debugger to the application
     *
     * @param IDebugger $debugger
     */
    public function addDebugger(IDebugger $debugger) {
        $this->debuggers[] = $debugger;
    }

    /**
     * Sends an error to the output.
     */
    public function outputError() {

    }
}