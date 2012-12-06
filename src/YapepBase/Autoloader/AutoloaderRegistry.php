<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Autoloader;


use YapepBase\Autoloader\AutoloaderAbstract;

/**
 * Handles adding and removing autoloaders to/from the application.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class AutoloaderRegistry {

	/**
	 * The autoloader instance for singleton-like operation.
	 *
	 * @var \YapepBase\Autoloader\AutoloaderRegistry
	 */
	protected static $instance;

	/**
	 * Stores the registered autoloader objects.
	 *
	 * @var array
	 */
	protected $registeredAutoloaders = array();

	/**
	 * Singleton instance getter.
	 *
	 * @return \YapepBase\Autoloader\AutoloaderRegistry
	 *
	 * @codeCoverageIgnore
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		spl_autoload_register(array($this, 'load'));
	}

	/**
	 * Clone method.
	 */
	protected function __clone() {
	}

	/**
	 * Adds a new autoloader object to the end of the list.
	 *
	 * @param \YapepBase\Autoloader\AutoloaderAbstract $autoloader   The autoloader object to register.
	 *
	 * @return void
	 */
	public function addAutoloader(AutoloaderAbstract $autoloader) {
		$this->registeredAutoloaders[] = $autoloader;
	}

	/**
	 * Adds a new autoloader object to the beginning of the list.
	 *
	 * @param \YapepBase\Autoloader\AutoloaderAbstract $autoloader   The autoloader object to register.
	 *
	 * @return void
	 */
	public function prependAutoloader(AutoloaderAbstract $autoloader) {
		array_unshift($this->registeredAutoloaders, $autoloader);
	}

	/**
	 * Clears all the registered autoloaders.
	 *
	 * @return void
	 */
	public function clear() {
		$this->registeredAutoloaders = array();
	}

	/**
	 * Runs through all Autoloaders and tries to load a class.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return bool   TRUE if the load was successful, FALSE on failure.
	 */
	public function load($className) {
		foreach ($this->registeredAutoloaders as $autoloader) {
			/** @var \YapepBase\Autoloader\AutoloaderAbstract $autoloader */
			if ($autoloader->load($className)) {
				return true;
			}
		}
		return false;
	}
}