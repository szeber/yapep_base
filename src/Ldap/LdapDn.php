<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Ldap
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Ldap;

use YapepBase\Exception\ParameterException;

/**
 * An object, that represents the distinguished name, which is the object's location in the LDAP tree.
 *
 * @package    YapepBase
 * @subpackage Ldap
 */
class LdapDn {

	/**
	 * Contains the elements in the DN
	 *
	 * @var array
	 */
	protected $elements = array();

	/**
	 * Constructs a DN object from parts in an associative array.
	 *
	 * @todo This array structure should be changed to something more controllable structure.
	 * @todo Maybe we should use an object, or we should create setter methods [emul]
	 *
	 * @param array $dn   DN parts as an array with subarrays in the array('id' => 'uid', 'value' => 'something')
	 *                        format.
	 */
	public function __construct($dn = array()) {
		if (is_array($dn)) {
			$this->parseDn($dn);
		}
	}

	/**
	 * Parses a string into the object.
	 *
	 * @param array $dn   The DN to parse.
	 *
	 * @return void
	 */
	public function parseDn($dn = array()) {
		$this->elements = array();
		foreach ($dn as $entry) {
			if (isset($entry['id']) && isset($entry['value'])) {
				$this->elements[] = array('id' => $entry['id'], 'value' => $entry['value']);
			}
		}
	}

	/**
	 * Returns the DN parts in the array('id' => 'ou', 'value' => 'something') format.
	 *
	 * @return array
	 */
	public function getParts() {
		return $this->elements;
	}

	/**
	 * Converts the DN object into a string.
	 *
	 * @return string
	 */
	public function __toString() {
		$elements = array();
		foreach ($this->elements as $element) {
			$elements[] = $this->escape($element['id']) . '=' . $this->escape($element['value']);
		}
		return implode(',', $elements);
	}

	/**
	 * Adds an element to the beginning (leaf part) of the DN string.
	 *
	 * @param string $id      Type of the element.
	 * @param string $value   Value of the element.
	 *
	 * @return \YapepBase\Ldap\LdapDn   The current LDAP DN instance.
	 */
	public function addLeafElement($id, $value) {
		array_unshift($this->elements, array('id' => $id, 'value' => $value));
		return $this;
	}

	/**
	 * Escapes a string for use in an LDAP DN
	 *
	 * @param string $string   The string to escape
	 *
	 * @return string
	 */
	public static function escape($string) {
		if (preg_match('/(\\|,|\+|=|"|<|>|#|;)/', $string)) {
			return '"' . strtr($string, array('"' => '\\"', '\\' => '\\\\')) . '"';
		}

		return $string;
	}

	/**
	 * Parses a DN string and returns the DN object, that represents it.
	 *
	 * @param string $string   The LDAP string to parse.
	 *
	 * @return \YapepBase\Ldap\LdapDn
	 *
	 * @throws \YapepBase\Exception\ParameterException
	 */
	public static function getFromString($string) {
		$parts = explode(',', $string);
		$elements = array();
		foreach ($parts as $part) {
			if (!preg_match('/^\s*([^\s]+)\s*=\s*([^\s]+)\s*$/', $part, $matches)) {
				throw new ParameterException('Invalid DN string: ' . $string);
			}
			$elements[] = array('id' => $matches[1], 'value' => $matches[2]);
		}
		return new static($elements);
	}
}