<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Janos Pasztor <net@janoszen.hu>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Ldap;

/**
 * Represents the data in a single entry in the LDAP database in a key-value store. If multiple values belong to an
 * attribute, it shall be stored in an array within the key.
 */
class LdapEntry {
	/**
	 * Contains the attributes in the entry.
	 * @var type 
	 */
	protected $attributes = array();
	/**
	 * Creates an LDAP entry from a key-value array. If multiple values belong to an
	 * attribute, it shall be stored in an array within the key.
	 * @param type $attributes 
	 */
	public function __construct($attributes = array()) {
		$this->setAsArray($attributes);
	}

	/**
	 * Sets the LDAP attributes from a key-value array. If multiple values belong to an
	 * attribute, it shall be stored in an array within the key.
	 * @param type $attributes 
	 */
	public function setAsArray($attributes) {
		$this->attributes = $attributes;
	}

	/**
	 * Returns the entry in a suitable way for ldap_add.
	 * @internal
	 * @return   array
	 */
	public function getAsArray() {
		return $this->attributes;
	}
}