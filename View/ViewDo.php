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

use YapepBase\View\ViewAbstract;
use YapepBase\Application;
use YapepBase\Mime\MimeType;
use YapepBase\Exception\Exception;

/**
 * A simple data storage object used by the View layer.
 *
 * @package    YapepBase
 * @subpackage View
 */
class ViewDo {

	/**
	 * The content type what should be considered before escaping the data. {@uses MimeType::*}
	 *
	 * @var string
	 */
	protected $contentType;

	/**
	 * Stores the raw data.
	 *
	 * @var array
	 */
	protected $dataRaw = array();

	/**
	 * Stores the escaped data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor
	 *
	 * @param string $contentType   The content type for the response. {@uses MimeType::*}
	 */
	public function __construct($contentType) {
		$this->contentType = $contentType;
	}

	/**
	 * Returns the the value registered to the given key.
	 *
	 * @param string $key   The name of the key.
	 * @param bool   $raw   if TRUE it will return the raw (unescaped) data.
	 *
	 * @return mixed   The data stored with the given key.
	 */
	final public function get($key, $raw = false) {
		if (empty($key)) {
			trigger_error('Empty key', E_USER_NOTICE);
			return;
		}

		$keyParts = explode('.', $key);

		// Storing a copy by refence which will be used
		if ($raw) {
			$target = &$this->dataRaw;
		}
		else {
			$target = &$this->data;
		}

		// TODO: This part should be extracted [emul]

		// Processing all of the keys except the last one
		for ($i = 0; $i < count($keyParts) - 1; $i++) {
			// If the key exists and its value is array
			if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
				// Then we overwrite the stores copy with the new child element
				$target = &$target[$keyParts[$i]];
			}
			else {
				// If it doesn't exist, or its not an array, it means that we can't go further,
				// so the requested key does not exist.
				trigger_error('Not defined value: ' . $key, E_USER_NOTICE);
				return null;
			}
		}

		// We reached the desired depth
		return $target[$keyParts[count($keyParts) - 1]];
	}

	/**
	 * Stores one ore more value(s).
	 *
	 * @param string $nameOrData   The name of the key, or the storable data in an associative array.
	 * @param mixed  $value        The value.
	 *
	 * @throws \YapepBase\Exception\Exception   If the key already exist.
	 *
	 * @return void
	 */
	public function set($nameOrData, $value = null) {
		if (is_array($nameOrData)) {
			foreach ($nameOrData as $key => $value) {
				$this->set($key, $value);
			}
		}
		else {
			if (array_key_exists($nameOrData, $this->data)) {
				throw new Exception('Key already exist: ' . $nameOrData);
			}
			$this->dataRaw[$nameOrData] = $value;
			$this->data[$nameOrData] = $this->escape($value);
		}
	}

	/**
	 * Clears all the stored data.
	 *
	 * @return void
	 */
	public function clear() {
		$this->data = array();
		$this->dataRaw = array();
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
		if (empty($key)) {
			trigger_error('Empty key', E_USER_NOTICE);
			return true;
		}
		$keyParts = explode('.', $key);

		// Storing a copy by refence which will be used
		$target = &$this->data;

		// Processing all of the keys except the last one
		for ($i = 0; $i < count($keyParts) - 1; $i++) {
			// If the key exists and its value is array
			if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
				// Then we overwrite the stores copy with the new child element
				$target = &$target[$keyParts[$i]];
			}
			else {
				// If it doesn't exist, or its not an array, it means that we can't go further,
				// so the requested key does not exist.
				return true;
			}
		}

		return $checkIsSet
			? !array_key_exists($keyParts[count($keyParts) - 1], $target)
			: empty($target[$keyParts[count($keyParts) - 1]]);
	}

	/**
	 * Checks if the value is an array.
	 *
	 * @param string $key   The name of the key.
	 *
	 * @return bool   TRUE if its an array, FALSE if not.
	 */
	public function checkIsArray($key) {
		$keyParts = explode('.', $key);

		// Storing a copy by refence which will be used
		$target = &$this->data;

		// Processing all of the keys except the last one
		for ($i = 0; $i < count($keyParts) - 1; $i++) {
			// If the key exists and its value is array
			if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
				// Then we overwrite the stores copy with the new child element
				$target = &$target[$keyParts[$i]];
			}
			else {
				// If it doesn't exist, or its not an array, it means that we can't go further,
				// so the requested key does not exist.
				return false;
			}
		}

		return isset($target[$keyParts[count($keyParts) - 1]])
			? is_array($target[$keyParts[count($keyParts) - 1]])
			: false;
	}

	/**
	 * A valasz formatuma szerint escapelo metodus.
	 *
	 * @param mixed $value   Az escapelendo adat.
	 *
	 * @return mixed   Az escapelt adat.
	 */
	protected function escape($value) {
		switch ($this->contentType) {
			case MimeType::HTML:
			default:
				return $this->escapeForHtml($value);
				break;
		}
	}

	/**
	 * Escapes the given parameter to HTML response. It escapes arrays recursively
	 *
	 * @param mixed $value   The data wat should be escaped.
	 *
	 * @return mixed   The escaped value.
	 */
	protected function escapeForHtml($value) {
		switch (gettype($value)) {
			case 'string':
				return htmlspecialchars($value);

			case 'array':
				foreach ($value as $elementKey => $elementValue) {
					$value[$elementKey] = $this->escape($elementValue);
				}

			default:
				return $value;
		}
	}
}