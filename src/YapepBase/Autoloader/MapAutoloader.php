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
 * Autoloader class which works by a map.
 *
 * You have to provide an array which links every class to their path.
 *
 * @package YapepBase\Autoloader
 */
class MapAutoloader implements IAutoloader {

	/**
	 * Class map.
	 *
	 * @var array
	 */
	protected $classMap = array();

	/**
	 * @param array $classMap   Array for the classes. The key is the name of the class and the value is the path.
	 */
	public function __construct(array $classMap) {
		$this->classMap = $classMap;
	}

	/**
	 * Loads the specified class if it can be found by name.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
	 */
	public function load($className) {
		try {
			if (array_key_exists($className, $this->classMap)) {
				include_once $this->classMap[$className];
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
}