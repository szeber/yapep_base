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
	 * The classpaths to use
	 *
	 * @var array
	 */
	protected $classpath = array();

	/**
	 * Set an array of directories to be tried on class loading.
	 *
	 * @param array $classpath   The classpath.
	 *
	 * @return AutoloaderBase
	 */
	public function setClassPath(array $classpath) {
		$this->classpath = $classpath;
		return $this;
	}

	/**
	 * Add a single directory or an array of directories to the list of dirctories to be tried on class loading.
	 *
	 * @param string|array $directory   The directory, or array of directories.
	 *
	 * @return AutoloaderBase
	 */
	public function addClassPath($directory) {
		if (is_array($directory)) {
			$this->classpath = array_merge($this->classpath, $directory);
		} else {
			$this->classpath[] = $directory;
		}
	}

	/**
	 * Returns all paths in the classpath
	 *
	 * @return array of string
	 */
	public function getClassPath() {
		return $this->classpath;
	}

	/**
	 * Loads the specified class if it can be found by name.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
	 */
	abstract public function load($className);

	/**
	 * Registers the autoloader with AutoloaderRegistry. This is an alias for
	 * \YapepBase\Autoloader\AutoloaderRegistry::getInstance()->register($autoloader);
	 *
	 * @return void
	 */
	public function register() {
		AutoloaderRegistry::getInstance()->register($this);
	}

	/**
	 * Unregisters the autoloader with AutoloaderRegistry. This is an alias for
	 * \YapepBase\Autoloader\AutoloaderRegistry::getInstance()->unregister($autoloader);
	 *
	 * @return void
	 */
	public function unregister() {
		AutoloaderRegistry::getInstance()->unregister($this);
	}
}