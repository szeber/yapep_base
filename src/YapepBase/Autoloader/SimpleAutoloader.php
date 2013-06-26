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

// We need this here, as at this point probably we don't have a working autoloader yet
require_once __DIR__ . '/IAutoloader.php';

/**
 * SimpleAutoloader class
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class SimpleAutoloader implements IAutoloader {

	/**
	 * The class paths to use
	 *
	 * @var array
	 */
	protected $classPaths = array();

	/**
	 * The class paths to use for given namespace prefixes.
	 *
	 * The key is tha namespace prefix, and the value is the path
	 *
	 * @var array
	 */
	protected $classPathsWithNamespace = array();

	/**
	 * Adds a path to use on class loading.
	 *
	 * @param array|string $path             The path(s) to use.
	 * @param string       $forceNameSpace   A full namespace. If given all the classes in a namespace having
	 *                                          this prefix will be searched at this path only.
	 *
	 * @return \YapepBase\Autoloader\IAutoloader
	 */
	public function addClassPath($path, $forceNameSpace = null) {
		foreach ((array)$path as $pathItem) {
			$pathItem = rtrim($pathItem, DIRECTORY_SEPARATOR);
			if (!is_null($forceNameSpace)) {

				$forceNameSpace = ltrim($forceNameSpace, '\\');
				$this->classPathsWithNamespace[$forceNameSpace] = $pathItem;
			}
			else {
				$this->classPaths[] = $pathItem;
			}
		}

		return $this;
	}

	/**
	 * Returns the possible full paths for the given class.
	 *
	 * @param string $className   Namespace and name of the class.
	 *
	 * @return array   Contains all the paths where it is possible to find the class.
	 */
	protected function getPaths($className) {
		$namespacePath = explode('\\', $className);
		$classNamePath = explode('_', array_pop($namespacePath));

		$namespace = implode('\\', $namespacePath);

		// If we have an exception for that namespace
		if (array_key_exists($namespace, $this->classPathsWithNamespace)) {
			return array(
				$this->classPathsWithNamespace[$namespace]
				. DIRECTORY_SEPARATOR
				. implode(DIRECTORY_SEPARATOR, array_merge($namespacePath, $classNamePath)) . '.php'
			);
		}

		$files = array();
		$fileName = implode(DIRECTORY_SEPARATOR, array_merge($namespacePath, $classNamePath)) . '.php';
		foreach ($this->classPaths as $path) {
			$files[] = $path . DIRECTORY_SEPARATOR . $fileName;
		}

		return $files;
	}

	/**
	 * Includes a file which should contain the class.
	 *
	 * @param string $fileName    Name of the file.
	 * @param string $className   Name of the class to search for in the file.
	 *
	 * @return bool   TRUE if the file exists, and can be opened and contains the given class or interface.
	 */
	protected function loadClass($fileName, $className) {
		try {
			if (is_file($fileName) && is_readable($fileName) && include_once $fileName) {
				if (class_exists($className, false) || interface_exists($className, false)) {
					return true;
				}
			}
		// If we have an ErrorHandler what converts ErrorExceptions from error, we have to handle that somehow
		// Because it will ruin the autoloader, and the actual error will be very hard to debug
		} catch (\ErrorException $e) {
			// TODO: Find a way to handle this situation [emul]
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
		foreach ($this->getPaths($className) as $fileName) {
			if ($this->loadClass($fileName, $className)) {
				return true;
			}
		}
		return false;
	}
}