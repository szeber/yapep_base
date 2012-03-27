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

/**
 * Handles adding and removing autoloaders to/from the application.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class AutoloaderRegistry {

	/**
	 * Registry used to store the autoloaders
	 *
	 * @var \SplObjectStorage
	 */
	protected $registry;

	/**
	 * Automatically register/unregister with SPL.
	 *
	 * @var bool default true
	 */
	protected $autoregister = true;

	/**
	 * The autoloader instance for singleton-like operation.
	 *
	 * @var \YapepBase\Autoloader\AutoloaderRegistry
	 */
	protected static $instance;

	/**
	 * Singleton instance getter.
	 *
	 * @return \YapepBase\Autoloader\AutoloaderRegistry
	 *
	 * @codeCoverageIgnore
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize object storage.
	 */
	public function __construct() {
		$this->registry = new \SplObjectStorage();
	}

	/**
	 * Sets or clears the flag to automatically register with SPL
	 *
	 * @param bool $autoregister
	 */
	public function setAutoregister($autoregister) {
		$this->autoregister = (bool)$autoregister;
	}

	/**
	 * Registers this registry with SPL.
	 *
	 * @codeCoverageIgnore
	 */
	public function registerWithSpl() {
		\spl_autoload_register(array($this, 'load'));
	}

	/**
	 * Unregisters this registry with SPL
	 *
	 * @codeCoverageIgnore
	 */
	public function unregisterFromSpl() {
		\spl_autoload_unregister(array($this, 'load'));
	}

	/**
	 * Runs through all Autoloaders and tries to load a class.
	 *
	 * @param  string  $className
	 *
	 * @return bool
	 */
	public function load($className) {
		foreach ($this->registry as $autoloader) {
			/** @var \YapepBase\Autoloader\AutoloaderBase $autoloader */
			if ($autoloader->load($className)) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Register an autoloader with the registry
	 *
	 * @param  \YapepBase\Autoloader\AutoloaderBase $object
	 * @param  bool                                 $autoregister   Automatically register with SPL.
	 */
	public function register(\YapepBase\Autoloader\AutoloaderBase $object, $autoregister = null) {
		if (($autoregister || $this->autoregister) && !$this->registry->count()) {
			$this->registerWithSpl();
		}
		$this->registry->attach($object);
	}
	/**
	 * Unregister from registry.
	 *
	 * @param \YapepBase\Autoloader\AutoloaderBase $autoloader
	 * @param bool                                 $autounregister   Automatically unregister from SPL if
	 *                                                               no more autoloaders are left.
	 */
	public function unregister($autoloader, $autounregister = null) {
		$this->registry->detach($autoloader);
		if (($autounregister || $this->autoregister) && !$this->registry->count()) {
			$this->unregisterFromSpl();
		}
	}
	/**
	 * Remove all autoloaders by class name.
	 *
	 * @param string $autoloaderClass
	 * @param bool   $autounregister    Automatically unregister from SPL if no more autoloaders are left.
	 */
	public function unregisterByClass($autoloaderClass, $autounregister = null) {
		$autoloaderClass = ltrim($autoloaderClass, '\\');
		foreach ($this->registry as $autoloader) {
			if (\get_class($autoloader) == $autoloaderClass) {
				$this->unregister($autoloader, $autounregister);
			}
		}
	}
}