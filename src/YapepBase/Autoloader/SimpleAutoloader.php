<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Autoloader
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Autoloader;

/**
 * SimpleAutoloader class
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class SimpleAutoloader extends AutoloaderBase {

	/**
	 * Gets possible file names for all directories to autoload.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return array
	 */
	protected function getFileNames($className) {
		$namespacePath = \explode('\\', $className);
		$classnamePath = \explode('_', \array_pop($namespacePath));
		$files = array();
		$fileName = \implode(\DIRECTORY_SEPARATOR, \array_merge($namespacePath, $classnamePath)) . '.php';
		foreach ($this->classpath as &$path) {
			$files[] = $path . \DIRECTORY_SEPARATOR . $fileName;
		}
		return $files;
	}

	/**
	 * Load a file which should contain a class.
	 *
	 * @param string $fileName    Name of the file.
	 * @param string $className   Name of the class.
	 *
	 * @return bool    TRUE if loading was successful.
	 */
	protected function loadFile($fileName, $className) {
		try {
			if (\is_file($fileName) && \is_readable($fileName) && include_once $fileName) {
				if (\class_exists($className, false) || \interface_exists($className, false)) {
					return true;
				}
			}
		} catch (\ErrorException $e) {
		}
		return false;
	}

	/**
	 * Loads the specified class if it can be found by name.
	 *
	 * @param string $className   Name of the class
	 *
	 * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
	 */
	public function load($className) {
		foreach ($this->getFileNames($className) as $fileName) {
			if ($this->loadFile($fileName, $className)) {
				return true;
			}
		}
		return false;
	}
}