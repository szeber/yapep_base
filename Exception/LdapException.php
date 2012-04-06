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

namespace YapepBase\Exception;

/**
 * This is the generic LDAP exception
 */
class LdapException extends \Exception {
	/**
	 * Extracts the error message and code from the connection
	 * @param   resource   $link 
	 */
	public function __construct($link) {
		parent::__construct(ldap_error($link), ldap_errno($link));
	}
}
