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
     * Stores the system DI container
     *
     * @var \YapepBase\DependencyInjection\SystemContainer
     */
    protected $diContainer;

    /**
     * Singleton constructor
     */
    protected function __construct() {
        $this->config = Config::getInstance();
        // Set up error handling
        $this->errorHandlerContainer = $this->getDiContainer()->getErrorHandlerContainer();
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
     * Sets the router used by the application.
     *
     * @param \YapepBase\Router\IRouter $router
     */
    public function setRouter(IRouter $router) {
        $this->router = $router;
    }

    /**
     * Returns the router used by the application.
     *
     * @return \YapepBase\Router\IRouter
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * Sets the DI contianer to be used by the application
     *
     * @param \YapepBase\DependencyInjection\SystemContainer $diContainer
     */
    public function setDiContainer(SystemContainer $diContainer) {
        $this->diContainer;
    }

    /**
     * Returns the DI container used by the application
     *
     * @return \YapepBase\DependencyInjection\SystemContainer
     */
    public function getDiContainer() {
        if (empty($this->diContainer)) {
            $this->diContainer = new SystemContainer();
        }
        return $this->diContainer;
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