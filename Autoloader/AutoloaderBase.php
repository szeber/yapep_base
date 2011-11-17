<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Autoloader
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Autoloader;
use YapepBase\Exception\Exception;

/**
 * Autoloader base abstract class
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
abstract class AutoloaderBase {

    /**
     * The autoloader instance
     *
     * @var AutoloaderBase
     */
    protected static $instance;

    /**
     * Loads the specified class if it can be found by name.
     *
     * @param string $className
     *
     * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
     */
    abstract public function load($className);

    /**
     * Registers the autoloader
     *
     * @throws YapepBase\Exception\YapepException   If the autoloader is already registered
     */
    public static function register() {
        if (!empty(static::$instance)) {
            throw new Exception('Trying to register an already registered autoloader');
        }
        static::$instance = new static();
        spl_autoload_register(array(static::$instance, 'load'), true, true);
    }

    /**
     * Unregisters the autoloader
     *
     * @throws YapepBase\Exception\YapepException   If the autoloader is not registered
     */
    public static function unregister() {
        if (is_null(static::$instance)) {
            throw new Exception('Trying to unregister autoloader, but it is not registered');
        }
        spl_autoload_unregister(array(static::$instance, 'load'));
        static::$instance = null;
    }

}