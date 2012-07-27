<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\View;

use YapepBase\View\BlockAbstract;

/**
 * ComponentAbstract class, should be extended by every Component.
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class ComponentAbstract extends BlockAbstract {

	/**
	 * Returns the key can be used to store and retrieve the data.
	 *
	 * @param string $key   The original key.
	 *
	 * @return string   The generated key.
	 */
	private function getKey($key) {
		return md5(__CLASS__) . '_' . $key;
	}

	/**
	 * Stores the given value with the given key.
	 *
	 * @param string $key     The name of the key.
	 * @param mixed  $value   The value.
	 *
	 * @throws \YapepBase\Exception\Exception   If the key already exist.
	 *
	 * @return void
	 */
	public function set($key, $value) {
		$this->getViewDo()->set($this->getKey($key), $value);
	}

	/**
	 * Returns the the value registered to the given key.
	 *
	 * @param string $key   The name of the key.
	 * @param bool   $raw   if TRUE it will return the raw (unescaped) data.
	 *
	 * @return mixed   The data stored with the given key.
	 */
	public function get($key, $raw = false) {
		return parent::get($this->getKey($key), $raw);
	}

	/**
	 * Checks the given key if it has a value.
	 *
	 * @param string $key          The name of the key.
	 * @param bool   $checkIsSet   If TRUE it checks the existense of the key.
	 *
	 * @return bool   FALSE if it has a value/exist, TRUE if not.
	 */
	public function checkIsEmpty($key, $checkIsSet = false) {
		return parent::checkIsEmpty($this->getKey($key), $checkIsSet);
	}

	/**
	 * Checks if the value is an array.
	 *
	 * @param string $key   The name of the key.
	 *
	 * @return bool   TRUE if its an array, FALSE if not.
	 */
	public function checkIsArray($key) {
		return parent::checkIsArray($this->getKey($key));
	}

	/**
	 * Returns the the value registered to the given key from the "scope" of the caller class.
	 *
	 * @param string $key   The name of the key.
	 * @param bool   $raw   if TRUE it will return the raw (unescaped) data.
	 *
	 * @return mixed   The data stored with the given key.
	 */
	public function getFromOrigin($key, $raw = false) {
		return parent::get($key, $raw);
	}

	/**
	 * Checks the given key if it has a value in the "scope" of the caller class.
	 *
	 * @param string $key          The name of the key.
	 * @param bool   $checkIsSet   If TRUE it checks the existense of the key.
	 *
	 * @return bool   FALSE if it has a value/exist, TRUE if not.
	 */
	public function checkIsEmptyFromOrigin($key, $checkIsSet = false) {
		return parent::checkIsEmpty($key, $checkIsSet);
	}

	/**
	 * Checks if the value is an array in the "scope" of the caller class.
	 *
	 * @param string $key   The name of the key.
	 *
	 * @return bool   TRUE if its an array, FALSE if not.
	 */
	public function checkIsArrayFromOrigin($key) {
		return parent::checkIsArray($key);
	}
}