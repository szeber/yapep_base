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

    /**
     * The singleton instance
     *
     * @var SystemContainer
     */
    protected static $instance;

    /**
     * Singleton constructor. Sets up the system DI objects.
     *
     * @return \YapepBase\Log\Message\ErrorMessage
     */
    protected function __construct() {
        $this[self::KEY_ERROR_LOG_MESSAGE] = function($container) {
            return new ErrorMessage();
        };
    }

    /**
     * Singleton clone method.
     */
    protected function __clone() {}

    /**
     * Singleton getter method.
     *
     * @return \YapepBase\DependencyInjection\SystemContainer
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Returns a logging ErrorMessage instance
     *
     * @return \YapepBase\Log\Message\ErrorMessage
     */
    public function getErrorLogMessage() {
        return $this[self::KEY_ERROR_LOG_MESSAGE];
    }

}