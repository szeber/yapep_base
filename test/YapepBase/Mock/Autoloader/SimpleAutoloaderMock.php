<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Autoloader;


use YapepBase\Autoloader\SimpleAutoloader;

/**
 * Mock class for the SimpleAutoloader.
 *
 * @package    YapepBase
 * @subpackage Mock\Autoloader
 */
class SimpleAutoloaderMock extends SimpleAutoloader {

	/**
	 * The class paths to use
	 *
	 * @var array
	 */
	public $classPaths = array();

	/**
	 * The class paths to use for given namespace prefixes.
	 *
	 * The key is tha namespace prefix, and the value is the path
	 *
	 * @var array
	 */
	public $classPathsWithNamespace = array();

	/**
	 * Returns the possible full paths for the given class.
	 *
	 * @param string $className   Namespace and name of the class.
	 *
	 * @return array   Contains all the paths where it is possible to find the class.
	 */
	public function getPaths($className) {
		return parent::getPaths($className);
	}

	/**
	 * Includes a file which should contain the class.
	 *
	 * @param string $fileName    Name of the file.
	 * @param string $className   Name of the class to search for in the file.
	 *
	 * @return bool   TRUE if the file exists, and can be opened and contains the given class or interface.
	 */
	public function loadClass($fileName, $className) {
		return parent::loadClass($fileName, $className);
	}
}