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

/** Require parent class */
require_once BASE_DIR . 'YapepBase/Autoloader/AutoloaderBase.php';

/**
 * SimpleAutoloader class
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class SimpleAutoloader extends AutoloaderBase {

    /**
     * The autoloader instance
     *
     * @var SimpleAutoloader
     */
    protected static $instance;

    /**
     * Loads the specified class if it can be found by name.
     *
     * @param string $className
     *
     * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
     */
    public function load($className) {
        $namespacePath = $this->sanitizeClassPath(explode('\\', $className));
        if (empty($namespacePath)) {
            return false;
        }
        $classnamePath = $this->sanitizeClassPath(explode('_', array_pop($namespacePath)));
        if (empty($classnamePath)) {
            return false;
        }
        $fileName = BASE_DIR . implode(DIRECTORY_SEPARATOR, array_merge($namespacePath, $classnamePath)) . '.php';
        if (is_file($fileName) || is_readable($fileName)) {
            require $fileName;
            return true;
        }
        return false;
    }

    /**
     * Removes potentialy dangerous parts from the array
     *
     * @param array $classPath
     *
     * @return array   The cleaned path array
     */
    protected function sanitizeClassPath(array $classPath) {
        foreach ($classPath as $index => $path) {
            if (strstr($path, '.') || strstr($path, '/')) {
                unset($classPath[$index]);
            }
        }
        return $classPath;
    }
}