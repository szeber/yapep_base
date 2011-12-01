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
use YapepBase\Response\IResponse;

use YapepBase\Request\IRequest;

use YapepBase\ErrorHandler\ErrorHandlerContainer;
use YapepBase\Lib\Pimple\Pimple;
use YapepBase\Log\Message\ErrorMessage;

/**
 * SystemContainer class
 *
 * @package    YapepBase
 * @subpackage DependencyInjection
 *
 * @todo Fix getController() and getBlock() implementation to use the namespace set in the config
 */
class SystemContainer extends Pimple {

    // Container keys
    /** Error log message key. */
    const KEY_ERROR_LOG_MESSAGE = 'errorLogMessage';
    /** Error handler container key. */
    const KEY_ERROR_HANDLER_CONTAINER = 'errorHandlerContainer';

    /**
     * Constructor. Sets up the system DI objects.
     *
     * @return \YapepBase\Log\Message\ErrorMessage
     */
    public function __construct() {
        $this[self::KEY_ERROR_LOG_MESSAGE] = function($container) {
            return new ErrorMessage();
        };
        $this[self::KEY_ERROR_HANDLER_CONTAINER] = function($container) {
            return new ErrorHandlerContainer();
        };
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
     * Returns an error handler container instance
     *
     * @return \YapepBase\ErrorHandler\ErrorHandlerContainer
     */
    public function getErrorHandlerContainer() {
        return $this[self::KEY_ERROR_HANDLER_CONTAINER];
    }

    /**
     * Returns a controller by it's name.
     *
     * @param stirng    $controllerName   The name of the controller class to return.
     *                                    (Without the namespace and Controller suffix)
     * @param IRequest  $request          The request object for the controller.
     * @param IResponse $response         The response object for the controller.
     *
     * @return \YapepBase\Controller\IController
     */
    public function getController($controllerName, IRequest $request, IResponse $response) {
        $fullClassName = '\YapepBase\Controller\\' . $controllerName . 'Controller';
        return new $fullClassName($request, $response);
    }

    /**
     * Returns a block by it's name
     *
     * @param string $blockName   The name of the block class to return.
     *                            (Without the namespace and Block suffix)
     *
     * @return \YapepBase\View\Block
     */
    public function getBlock($blockName) {
        $fullClassName = '\YapepBase\View\Block\\' . $blockName . 'Block';
        return new $fullClassName();
    }

}