<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   DependencyInjection
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\DependencyInjection;
use YapepBase\Exception\ViewException;

use YapepBase\Exception\ControllerException;

use YapepBase\Exception\DiException;

use YapepBase\Exception\Exception;

use YapepBase\Session\SessionRegistry;

use YapepBase\Event\EventHandlerRegistry;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\ErrorHandler\ErrorHandlerRegistry;
use YapepBase\Lib\Pimple\Pimple;
use YapepBase\Log\Message\ErrorMessage;

/**
 * SystemContainer class
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 */
class SystemContainer extends Pimple {

    // Container keys
    /** Error log message key. */
    const KEY_ERROR_LOG_MESSAGE = 'errorLogMessage';
    /** Error handler registry key. */
    const KEY_ERROR_HANDLER_REGISTRY = 'errorHandlerRegistry';
    /** Event handler container key. */
    const KEY_EVENT_HANDLER_REGISTRY = 'eventHandlerRegistry';
    /** Session registry key. */
    const KEY_SESSION_REGISTRY = 'sessionRegistry';
    /** Memcache key. */
    const KEY_MEMCACHE = 'memcache';
    /** Memcache key. */
    const KEY_MEMCACHED = 'memcached';

    const NAMESPACE_SEARCH_BLOCK = 'block';
    const NAMESPACE_SEARCH_TEMPLATE = 'template';
    const NAMESPACE_SEARCH_LAYOUT = 'layout';
    const NAMESPACE_SEARCH_CONTROLLER = 'controller';


    /**
     * List of namespaces to search in for each namespace search.
     * @var array
     */
    protected $searchNamespaces = array(
        self::NAMESPACE_SEARCH_BLOCK => array(),
        self::NAMESPACE_SEARCH_TEMPLATE => array(),
        self::NAMESPACE_SEARCH_LAYOUT => array(),
        self::NAMESPACE_SEARCH_CONTROLLER => array(),
    );

    /**
     * Constructor. Sets up the system DI objects.
     *
     * @return \YapepBase\Log\Message\ErrorMessage
     */
    public function __construct() {
        $this[self::KEY_ERROR_LOG_MESSAGE] = function($container) {
            return new ErrorMessage();
        };
        $this[self::KEY_ERROR_HANDLER_REGISTRY] = function($container) {
            return new ErrorHandlerRegistry();
        };
        $this[self::KEY_EVENT_HANDLER_REGISTRY] = $this->share(function($container) {
            return new EventHandlerRegistry();
        });
        $this[self::KEY_SESSION_REGISTRY] = $this->share(function($container) {
            return new SessionRegistry();
        });
        if (class_exists('\Memcache')) {
            $this[self::KEY_MEMCACHE] = function($container) {
                return new \Memcache();
            };
        }
        if (class_exists('\Memcached')) {
            $this[self::KEY_MEMCACHED] = function($container) {
                return new \Memcached();
            };
        }
    }

    /**
     * Returns a logging ErrorMessage instance
     *
     * @return \YapepBase\Log\Message\ErrorMessage
     */
    public function getErrorLogMessage() {
        return $this[self::KEY_ERROR_LOG_MESSAGE];
    }

    /**
     * Returns an error handler registry instance
     *
     * @return \YapepBase\ErrorHandler\ErrorHandlerRegistry
     */
    public function getErrorHandlerRegistry() {
        return $this[self::KEY_ERROR_HANDLER_REGISTRY];
    }

    /**
     * Returns an event handler registry instance
     *
     * @return \YapepBase\Event\EventHandlerRegistry
     */
    public function getEventHandlerRegistry() {
        return $this[self::KEY_EVENT_HANDLER_REGISTRY];
    }

    /**
     * Returns a session registry instance
     *
     * @return \YapepBase\Session\SessionRegistry
     */
    public function getSessionRegistry() {
        return $this[self::KEY_SESSION_REGISTRY];
    }

    /**
     * Returns a memcache instance
     *
     * @return \Memcache
     *
     * @throws \YapepBase\Exception\Exception   If there is no Memcache support in PHP.
     */
    public function getMemcache() {
        // @codeCoverageIgnoreStart
        if (!isset($this[self::KEY_MEMCACHE])) {
            throw new Exception('No memcache support in PHP');
        }
        // @codeCoverageIgnoreEnd
        return $this[self::KEY_MEMCACHE];
    }

    /**
     * Returns a memcache instance
     *
     * @return \Memcached
     *
     * @throws \YapepBase\Exception\Exception   If there is no Memcached support in PHP.
     */
    public function getMemcached() {
        // @codeCoverageIgnoreStart
        if (!isset($this[self::KEY_MEMCACHED])) {
            throw new Exception('No memcached support in PHP');
        }
        // @codeCoverageIgnoreEnd
        return $this[self::KEY_MEMCACHED];
    }

    /**
     * Adds a namespace to the namespace roots for the given type, to search classes in.
     *
     * @param string $type
     * @param string $namespace
     */
    public function addSearchNamespace($type, $namespace) {
        $this->searchNamespaces[$type][] = $namespace;
    }

    /**
     * Sets a list of namespace roots to search the given type of classes in.
     *
     * @param string $type         The type of class to search for {@uses self::NAMESPACE_SEARCH_*}
     * @param array  $namespaces   The list of namespaces
     */
    public function setSearchNamespaces($type, array $namespaces = array()) {
        $this->searchNamespaces[$type] = $namespaces;
    }

    /**
     * Searches for the class in all the search namespaces for the given type
     *
     * @param string $type             The type of class to search for. {@uses self::NAMESPACE_SEARCH_*}
     * @param string $controllerName
     *
     * @return string controller name
     *
     * @throws \YapepBase\Exception\DiException           If the class is not found
     */
    protected function searchForClass($type, $className) {
        if (isset($this->searchNamespaces[$type]) && is_array($this->searchNamespaces[$type])) {
            foreach ($this->searchNamespaces[$type] as $nsroot) {
                $fullName = $nsroot . '\\' . $className;
                if (\class_exists($fullName, true)) {
                    return $fullName;
                }
            }
        }
        throw new DiException('Class ' . $className . ' not found.', DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND);

    }

    /**
     * Searches for the controller in all the controller search namespaces
     *
     * @param  string $controllerName
     *
     * @return string controller name
     *
     * @throws \YapepBase\Exception\ControllerException   If the controller was not found
     */
    protected function searchForController($controllerName) {
        try {
            return $this->searchForClass(self::NAMESPACE_SEARCH_CONTROLLER, $controllerName . 'Controller');
        } catch (DiException $e) {
            throw new ControllerException('Controller ' . $controllerName . ' not found in '
                . \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_CONTROLLER]),
                ControllerException::ERR_CONTROLLER_NOT_FOUND);
        }
    }

    /**
     * Searches for the block in all the block search namespaces
     *
     * @param string $blockName
     *
     * @return string block name
     *
     * @throws \YapepBase\Exception\ViewException If the block was not found
     */
    protected function searchForBlock($blockName) {
        try {
            return $this->searchForClass(self::NAMESPACE_SEARCH_BLOCK, $blockName . 'Block');
        } catch (DiException $e) {
            throw new ViewException('Block ' . $blockName . ' not found in '
                . \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_BLOCK]),
                ViewException::ERR_BLOCK_NOT_FOUND);
        }
    }

    /**
     * Searches for the template in all the template search namespaces
     *
     * @param string $templateName
     *
     * @return string template name
     *
     * @throws \YapepBase\Exception\ViewException If the template was not found
     */
    protected function searchForTemplate($templateName) {
        try {
            return $this->searchForClass(self::NAMESPACE_SEARCH_TEMPLATE, $templateName . 'Template');
        } catch (DiException $e) {
            throw new ViewException('Template ' . $templateName . ' not found in '
                . \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_TEMPLATE]),
                ViewException::ERR_TEMPLATE_NOT_FOUND);
        }
    }

    /**
     * Searches for the layout in all the layout search namespaces
     *
     * @param string $layoutName
     *
     * @return string layout name
     *
     * @throws \YapepBase\Exception\ViewException If the template was not found
     */
    protected function searchForLayout($layoutName) {
        try {
            return $this->searchForClass(self::NAMESPACE_SEARCH_LAYOUT, $layoutName . 'Layout');
        } catch (DiException $e) {
            throw new ViewException('Layout ' . $layoutName . ' not found in '
                . \implode('; ', $this->searchNamespaces[self::NAMESPACE_SEARCH_LAYOUT]),
                ViewException::ERR_LAYOUT_NOT_FOUND);
        }
    }

    /**
     * Returns a controller by it's name.
     *
     * @param string    $controllerName   The name of the controller class to return.
     *                                    (Without the namespace and Controller suffix)
     * @param IRequest  $request          The request object for the controller.
     * @param IResponse $response         The response object for the controller.
     *
     * @return \YapepBase\Controller\IController
     *
     * @throws \YapepBase\Exception\ControllerException   If the controller was not found
     */
    public function getController($controllerName, IRequest $request, IResponse $response) {
        $fullClassName = $this->searchForController($controllerName);
        return new $fullClassName($request, $response);
    }

    /**
     * Returns a block by it's name
     *
     * @param string $blockName   The name of the block class to return.
     *                            (Without the namespace and Block suffix)
     *
     * @return \YapepBase\View\Block
     *
     * @throws \YapepBase\Exception\ViewException If the block was not found
     */
    public function getBlock($blockName) {
        $fullClassName = $this->searchForBlock($blockName);
        return new $fullClassName();
    }

    /**
     * Returns a template by it's name
     *
     * @param string $templateName   The name of the template class to return.
     *                               (Without the namespace and Template suffix)
     *
     * @return \YapepBase\View\Template
     *
     * @throws \YapepBase\Exception\ViewException If the block was not found
     */
    public function getTemplate($templateName) {
        $fullClassName = $this->searchForTemplate($templateName);
        return new $fullClassName();
    }

    /**
     * Returns a layout by it's name
     *
     * @param string $templateName   The name of the template class to return.
     *                               (Without the namespace and Template suffix)
     *
     * @return \YapepBase\View\Layout
     *
     * @throws \YapepBase\Exception\ViewException If the layout was not found
     */
    public function getLayout($layoutName) {
        $fullClassName = $this->searchForLayout($layoutName);
        return new $fullClassName();
    }

    /**
     * Set a list of namespace roots to search for controllers in.
     *
     * @param array $namespaces a list of namespace roots to search for the controller in.
     *
     * @deprecated   Use setSearchNamespaces() instead
     *
     * @codeCoverageIgnore
     */
    public function setControllerSearchNamespaces(array $namespaces = array()) {
        trigger_error(__METHOD__ . ' called, use ' . __CLASS__ . '::setSearchNamespaces instead', E_USER_DEPRECATED);
        $this->setSearchNamespaces(self::NAMESPACE_SEARCH_CONTROLLER, $namespaces);
    }

    /**
     * Adds a namespace to the namespace roots to search for controllers in.
     *
     * @param string $namespace a single namespace to add to the search list
     *
     * @deprecated   Use addSearchNamespace() instead
     *
     * @codeCoverageIgnore
     */
    public function addControllerSearchNamespace($namespace) {
        trigger_error(__METHOD__ . ' called, use ' . __CLASS__ . '::addSearchNamespace instead', E_USER_DEPRECATED);
        $this->addSearchNamespace(self::NAMESPACE_SEARCH_CONTROLLER, $namespace);
    }

    /**
     * Set a list of namespace roots to search for controllers in.
     *
     * @param array $namespaces a list of namespace roots to search for the controller in.
     *
     * @deprecated   Use setSearchNamespaces() instead
     *
     * @codeCoverageIgnore
     */
    public function setBlockSearchNamespaces($namespaces = array()) {
        trigger_error(__METHOD__ . ' called, use ' . __CLASS__ . '::setSearchNamespaces instead', E_USER_DEPRECATED);
        $this->setSearchNamespaces(self::NAMESPACE_SEARCH_BLOCK, $namespaces);
    }

    /**
     * Adds a namespace to the namespace roots to search for blocks in.
     *
     * @param string $namespace a single namespace to add to the search list
     *
     * @deprecated   Use addSearchNamespace() instead
     *
     * @codeCoverageIgnore
     */
    public function addBlockSearchNamespace($namespace) {
        trigger_error(__METHOD__ . ' called, use ' . __CLASS__ . '::addSearchNamespace instead', E_USER_DEPRECATED);
        $this->addSearchNamespace(self::NAMESPACE_SEARCH_BLOCK, $namespace);
    }


}