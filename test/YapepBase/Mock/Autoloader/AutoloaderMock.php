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


use YapepBase\Autoloader\AutoloaderAbstract;

/**
 * Mock class for the AutoloaderAbstract.
 *
 * @package    YapepBase
 * @subpackage Mock\Autoloader
 */
class AutoloaderMock extends AutoloaderAbstract {

	/**
	 * Stores the classes already loaded.
	 *
	 * @var array
	 */
	public $loadedClasses = array();

	/**
	 * If TRUE the load() always respond TRUE, if FALSE load() will always respond FALSE.
	 *
	 * @var bool
	 */
	public $isAbleToLoad = false;

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
	 * The identifier of the object.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Constructor.
	 *
	 * @param int $id              The id of the object for identification purpose.
	 * @param bool $isAbleToLoad   If TRUE the load() always respond TRUE, if FALSE load() will always respond FALSE.
	 */
	public function __construct($id = 1, $isAbleToLoad = true) {
		$this->id = $id;
		$this->isAbleToLoad = $isAbleToLoad;
	}

	/**
	 * Loads the specified class if it can be found by name.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
	 */
	public function load($className) {
		if (!$this->isAbleToLoad) {
			return false;
		}
		$this->loadedClasses[] = $className;
		return true;
	}
}