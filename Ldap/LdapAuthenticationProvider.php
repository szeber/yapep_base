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

class LdapAuthenticationProvider {
	/**
	 * Try authentication binding with the user. 
	 */
	const AUTHMODE_BIND = 0;
	/**
	 * Try authentication using a search user. Works only with plain text passwords.
	 */
	const AUTHMODE_SEARCH = 1;
	
	/**
	 * Perform the group check using the bind parameters from the authentication phase. If authmode is set to BIND,
	 * the user must have permissions to read group attributes.
	 */
	const GROUPMODE_USER = 0;
	
	/**
	 * Rebind for the group check with a new user.
	 */
	const GROUPMODE_REBIND = 1;
	
	/**
	 * Sets the authentication mode. See self::AUTHMODE_* for details.
	 * @param   int   $authMode 
	 */
	public function setAuthMode($authMode) {
		
	}
	
	/**
	 * Set the mode for group check. See self::GROUPMODE_* for details.
	 * @param   int   $groupMode
	 */
	public function setGroupMode($groupMode) {
		
	}
	
	/**
	 * Sets the base DN for usernames.
	 * @param   LdapDn   $dn
	 */
	public function setUserDn(LdapDn $dn) {
		
	}
	
	/**
	 * Sets the base DN for groups.
	 */
	public function setGroupDn(LdapDn $dn) {
		
	}
	
	/**
	 * Sets the user attribute to bind/search with.
	 * @param   string   $attribute 
	 */
	public function setUserAttribute($attribute) {
		
	}
	
	/**
	 * Set the group attribute to search for.
	 * @param   string   $attribute 
	 */
	public function setGroupAttribute($attribute) {
		
	}
	
	/**
	 * Sets the group attribute on the user if any and specifies, if it is a full DN.
	 */
	public function setGroupAttributeOnUser($attribute, $isDn) {
		
	}

	/**
	 * Sets the user attribute on the group if any and specifies, if it is a full DN.
	 */
	public function setUserAttributeOnGroup($attribute, $isDn) {
		
	}

	/**
	 * Set bind parameters for AUTHMODE_SEARCH and GROUPMODE_REBIND.
	 * @param   LdapDn   $binddn
	 * @param   string   $password 
	 */
	public function setBindParams(LdapDn $binddn, $password) {
		
	}
	
	/**
	 * Sets the required group name.
	 * @param   string   @group
	 */
	public function setRequiredGroup($group) {
		
	}
	
	/**
	 * Authenticates and authorizes the user. 
	 * @param   string   $username
	 * @param   string   $password
	 * @return  bool
	 */
	public function authenticateAndAuthorize($username, $password) {
		
	}
}
