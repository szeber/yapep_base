<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   DependencyInjection
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\DependencyInjection;

use YapepBase\Exception\ParameterException;

/**
 * Container main class.
 *
 * @package      YapepBase
 * @subpackage   DependencyInjection
 */
class Container implements \ArrayAccess {

	/**
	 * The stored values.
	 *
	 * @var array
	 */
	private $values;

	/**
	 * Constructor
	 *
	 * @param array $values   The parameters or objects.
	 */
	public function __construct(array $values = array()) {
		$this->values = $values;
	}

	/**
	 * Sets a parameter or an object.
	 *
	 * @param string $key     The unique key for the parameter or object.
	 * @param mixed  $value   The value of the parameter or a closure to defined an object.
	 *
	 * @return void
	 */
	public function offsetSet($key, $value) {
		$this->values[$key] = $value;
	}

	/**
	 * Gets a parameter or an object.
	 *
	 * @param string $key   The key of the parameter or object.
	 *
	 * @return mixed   The value of the parameter or an object
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the key is not defined
	 */
	public function offsetGet($key) {
		if (!array_key_exists($key, $this->values)) {
			throw new ParameterException('Unknown key: ' . $key);
		}

		return $this->values[$key] instanceof \Closure
			? $this->values[$key]($this)
			: $this->values[$key];
	}

	/**
	 * Checks if a parameter or an object is set.
	 *
	 * @param string $key   The key of the parameter or object.
	 *
	 * @return bool
	 */
	public function offsetExists($key) {
		return isset($this->values[$key]);
	}

	/**
	 * Unsets a parameter or an object.
	 *
	 * @param string $key   The key of the parameter or object.
	 *
	 * @return void
	 */
	public function offsetUnset($key) {
		unset($this->values[$key]);
	}

	/**
	 * Returns a closure that stores the result of the given closure for
	 * uniqueness in the scope of this instance of Container.
	 *
	 * @param \Closure $callable   A closure to wrap for uniqueness
	 *
	 * @return \Closure   The wrapped closure
	 */
	public function share(\Closure $callable) {
		return function ($innerCallable) use ($callable) {
			static $object;

			if (is_null($object)) {
				$object = $callable($innerCallable);
			}

			return $object;
		};
	}

	/**
	 * Protects a callable from being interpreted as a service.
	 *
	 * This is useful when you want to store a callable as a parameter.
	 *
	 * @param \Closure $callable   A closure to protect from being evaluated.
	 *
	 * @return \Closure   The protected closure.
	 */
	public function protect(\Closure $callable) {
		return function () use ($callable) {
			return $callable;
		};
	}

	/**
	 * Gets a parameter as it, without evaluating it.
	 *
	 * @param string $key   The of the parameter or object.
	 *
	 * @return mixed   The value of the parameter or the closure defining an object.
	 *
	 * @throws ParameterException   If the key is not defined.
	 */
	public function getRaw($key) {
		if (!array_key_exists($key, $this->values)) {
			throw new ParameterException('Unknown key: ' . $key);
		}

		return $this->values[$key];
	}
}
