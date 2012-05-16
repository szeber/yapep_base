<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Exception
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Ldap;

/**
 * This class provides an authentication and authoriziation solution against an LDAP database. OpenLDAP and
 * ActiveDirectory are both supported.
 *
 * AAA is performed as follows:
 *
 * - If the authentication mode is search, a pre-set DN and password is used for binding. In this case only
 *   plain text passwords work. The authentication is performed using an LDAP search.
 * - If the authentication mode is bind, the provided username and password are used to bind against the LDAP server.
 *
 * - If the group mode is rebind, the pre-set DN is used for re-binding to the LDAP server. The group membership search
 *   is then performed using that user.
 * - If the group mode is user, the previously authenticated user is used for searching for group membership.
 *
 * Important: due to the nature of LDAP, the application cannot verify potentially wrong configuration. If the ACL's
 * are not set up correctly for the chosen operation mode, you might get empty results, which will return a failure for
 * the AAA process. If the LdapAuthenticationProvider detects a verifiably wrong configuration, it will throw a
 * \YapepBase\Exception\ParameterException.
 *
 * Examples:
 *
 * Using LdapAuthenticationProvider against a standard OpenLDAP database:
 *
 * $connection = new LdapConnection();
 * $aaa        = new LdapAuthenticationProvider($connection);
 * $aaa->setAuthMode(LdapAuthenticationProvider::AUTHMODE_BIND);
 * $aaa->setGroupMode(LdapAuthenticationProvider::AUTHMODE_USER);
 * $aaa->setUserDn(new LdapDn(array(
 *     array('id' => 'ou', 'value' => 'Users'),
 *     array('id' => 'dc', 'value' => 'example'),
 *     array('id' => 'dc', 'value' => 'com'),
 * )));
 * $aaa->setGroupDn(new LdapDn(array(
 *     array('id' => 'ou', 'value' => 'Groups'),
 *     array('id' => 'dc', 'value' => 'example'),
 *     array('id' => 'dc', 'value' => 'com'),
 * )));
 * $aaa->setUserAttributeOnGroup('member', true);
 * $aaa->setRequiredGroup('Moderators');
 * if ($aaa->authenticate ($username, $password)) {
 *     //do something
 * } else {
 *     //so something else
 * }
 *
 * @package    YapepBase
 * @subpackage Ldap
 */
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
	 * LDAP connection to user for AAA.
	 *
	 * @var \YapepBase\Ldap\LdapConnection
	 */
	protected $connection;

	/**
	 * Authentication mode. See self::AUTHMODE_* for details.
	 *
	 * @var int
	 */
	protected $authMode;

	/**
	 * Group authorization mode {@uses self::GROUPMODE_* }.
	 *
	 * @var int
	 */
	protected $groupMode;

	/**
	 * User base DN.
	 *
	 * @var \YapepBase\Ldap\LdapDn
	 */
	protected $userDn;

	/**
	 * Group base DN.
	 *
	 * @var \YapepBase\Ldap\LdapDn
	 */
	protected $groupDn;

	/**
	 * The structural attribute for the user.
	 *
	 * @var string
	 */
	protected $userAttribute;

	/**
	 * The attribute, that contains the user's password.
	 *
	 * @var string
	 */
	protected $userPasswordAttribute = 'userPassword';

	/**
	 * The structural attribute for the grup.
	 *
	 * @var string
	 */
	protected $groupAttribute;

	/**
	 * The attribute on the group that identifies the user.
	 *
	 * @var string
	 */
	protected $groupAttributeOnUser;

	/**
	 * The group attribute on the user object is a full DN
	 *
	 * @var bool
	 */
	protected $groupAttributeOnUserIsDn;

	/**
	 * The attribute on the user that identifies the group.
	 *
	 * @var string
	 */
	protected $userAttributeOnGroup;

	/**
	 * The user attribute on the group object is a full DN
	 *
	 * @var bool
	 */
	protected $userAttributeOnGroupIsDn;

	/**
	 * The DN to use for binding.
	 *
	 * @var LdapDn
	 */
	protected $bindDn;

	/**
	 * The password to use for binding.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * The required group for authorization.
	 *
	 * @var string
	 */
	protected $requiredGroup;

	/**
	 * Sets up the connection to run queries against.
	 *
	 * @param LdapConnection $connection   The connection which will be used.
	 */
	public function __construct(LdapConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * Sets the authentication mode.
	 *
	 * @param int $authMode   {@uses self::AUTHMODE_* }.
	 *
	 * @return void
	 */
	public function setAuthMode($authMode) {
		switch ($authMode) {
			case self::AUTHMODE_BIND:
				//Fall through
			case self::AUTHMODE_SEARCH:
				$this->authMode = $authMode;
				break;
		}
	}

	/**
	 * Set the mode for group check.
	 *
	 * @param int $groupMode   {@uses self::GROUPMODE_*}
	 *
	 * @return void
	 */
	public function setGroupMode($groupMode) {
		switch ($groupMode) {
			case self::GROUPMODE_USER:
				// Fall through
			case self::GROUPMODE_REBIND:
				$this->groupMode = $groupMode;
				break;
		}
	}

	/**
	 * Sets the base DN for usernames.
	 *
	 * @param LdapDn $dn   The base DN.
	 *
	 * @return void
	 */
	public function setUserDn(LdapDn $dn) {
		$this->userDn = $dn;
	}

	/**
	 * Sets the base DN for groups.
	 *
	 * @param LdapDn $dn   The base DN.
	 *
	 * @return void
	 */
	public function setGroupDn(LdapDn $dn) {
		$this->groupDn = $dn;
	}

	/**
	 * Sets the user attribute to bind/search with.
	 *
	 * @param string $attribute   The attribute.
	 *
	 * @return void
	 */
	public function setUserAttribute($attribute) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->userAttribute = (string)$attribute;
	}

	/**
	 * Sets the userPassword attribute. Only user with self::AUTHMODE_SEARCH, defaults to userPassword.
	 *
	 * @param string $attribute   The attribute.
	 *
	 * @return void
	 */
	public function setUserPasswordAttribute($attribute) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->userPasswordAttribute = (string)$attribute;
	}

	/**
	 * Set the group attribute to search for.
	 *
	 * @param string $attribute   The attribute.
	 *
	 * @return void
	 */
	public function setGroupAttribute($attribute) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->groupAttribute = (string)$attribute;
	}

	/**
	 * Sets the group attribute on the user if any and specifies, if it is a full DN.
	 *
	 * @param string $attribute   The attribute.
	 * @param bool   $isDn        The attribute contains a full DN.
	 *
	 * @return void
	 */
	public function setGroupAttributeOnUser($attribute, $isDn) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->groupAttributeOnUser     = (string)$attribute;
		$this->groupAttributeOnUserIsDn = (bool)$isDn;
	}

	/**
	 * Sets the user attribute on the group if any and specifies, if it is a full DN.
	 *
	 * @param string $attribute   The attribute.
	 * @param bool   $isDn        The attribute contains a full DN.
	 *
	 * @return void
	 */
	public function setUserAttributeOnGroup($attribute, $isDn) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->userAttributeOnGroup     = (string)$attribute;
		$this->userAttributeOnGroupIsDn = (bool)$isDn;
	}

	/**
	 * Sets bind parameters for AUTHMODE_SEARCH and GROUPMODE_REBIND.
	 *
	 * @param LdapDn $bindDn     The DN to bind to.
	 * @param string $password   The password.
	 *
	 * @return void
	 */
	public function setBindParams(LdapDn $bindDn, $password) {
		$this->bindDn   = $bindDn;
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->password = (string)$password;
	}

	/**
	 * Sets the required group name. If it is not called, authorization is not performed.
	 *
	 * @param string $group   The group name.
	 *
	 * @return void
	 */
	public function setRequiredGroup($group) {
		// TODO: Casting should be replaced by type checking and ParameterException throwing [emul]
		$this->requiredGroup = (string)$group;
	}

	/**
	 * Authenticates and authorizes the user.
	 *
	 * @param string $username   The username.
	 * @param string $password   The password.
	 *
	 * @return bool   TRUE if the given user is autheticated and authorized.
	 */
	public function authenticateAndAuthorize($username, $password) {
		if (!$this->authenticate($username, $password)) {
			return false;
		}

		if (!$this->authorize($username)) {
			return false;
		}

		return true;
	}

	/**
	 * Builds a user LdapDn object.
	 *
	 * @param string $username   The username.
	 *
	 * @return LdapDn
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the configuration is verifiably wrong.
	 */
	protected function buildUserDn($username) {
		if (!$this->userDn) {
			throw new \YapepBase\Exception\ParameterException('User root DN is not configured. Please call ' .
				'LdapAuthenticationProvider::setUserDn() before calling authenticateAndAuthorize()');
		}
		if (!$this->userAttribute) {
			throw new \YapepBase\Exception\ParameterException('User attribute is not configured. Please call ' .
				'LdapAuthenticationProvider::setUserAttribute() before calling authenticateAndAuthorize()');
		}

		$dn = clone $this->userDn;
		$parts = $dn->getParts();
		$firstPart = array('id' => $this->userAttribute, 'value' => $username);

		array_unshift($parts, $firstPart);
		$dn->parseDn($parts);
		return $dn;
	}

	/**
	 * Builds a group LdapDn object.
	 *
	 * @param string $group   The group name.
	 *
	 * @return  LdapDn
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the configuration is verifiably wrong.
	 */
	protected function buildGroupDn($group) {
		if (!$this->groupDn) {
			throw new \YapepBase\Exception\ParameterException('Group root DN is not configured. Please call ' .
				'LdapAuthenticationProvider::setGroupDn() before calling authenticateAndAuthorize()');
		}
		if (!$this->groupAttribute) {
			throw new \YapepBase\Exception\ParameterException('Group attribute is not configured. Please call ' .
				'LdapAuthenticationProvider::setGroupAttribute() before calling authenticateAndAuthorize()');
		}

		$dn = clone $this->groupDn;
		$parts = $dn->getParts();
		$firstPart = array('id' => $this->groupAttribute, 'value' => $group);

		array_unshift($parts, $firstPart);
		$dn->parseDn($parts);
		return $dn;
	}

	/**
	 * Authenticates a user against an LDAP database.
	 *
	 * @param string $username   The username.
	 * @param string $password   The password.
	 *
	 * @return bool
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the configuration is verifiably wrong.
	 */
	protected function authenticate($username, $password) {
		// TODO: Some inline documentation would be nice, to easily understand whats happening [emul]

		if (!is_int($this->authMode)) {
			throw new \YapepBase\Exception\ParameterException('Authentication mode is not configured. Please call ' .
				'LdapAuthenticationProvider::setAuthMode() before calling authenticateAndAuthorize()');
		}

		if ($this->authMode == self::AUTHMODE_BIND) {
			$dn = $this->buildUserDn($username);
			try {
				$this->connection->bind($dn, $password);
			} catch (\YapepBase\Exception\LdapBindException $e) {
				return false;
			}
			return true;
		} elseif ($this->authMode == self::AUTHMODE_SEARCH) {
			try {
				$this->connection->bind($this->bindDn, $this->password);
			} catch (\YapepBase\Exception\LdapBindException $e) {
				throw new \YapepBase\Exception\ParameterException('Can\'t bind to LDAP server with the given ' .
					'credentials.');
			}
			$base = $this->buildUserDn($username);
			$filter = array($this->userPasswordAttribute . '=:_userPassword');
			$filterparams = array('userPassword' => $password);
			$results = $this->connection->search(
				$base,
				$filter,
				$filterparams,
				array('dn'),
				LdapConnection::DEREF_NEVER,
				LdapConnection::SCOPE_SUB);

			// TODO: Not really an issue, but an empty() is enough here i think, and its much faster [emul]
			if (count($results)) {
				return true;
			} else {
				return false;
			}
		} else {
			throw new \YapepBase\Exception\ParameterException('Invalid authentication mode configured. Please call ' .
				'LdapAuthenticationProvider::setAuthMode() before calling authenticateAndAuthorize()');
		}
	}

	/**
	 * Authorize after a successful authentication.
	 *
	 * @param string $username   The username.
	 *
	 * @return bool
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the configuration is verifiably wrong.
	 */
	protected function authorize($username) {
		// TODO: Some inline documentation would be nice, to easily understand whats happening [emul]

		if (!$this->requiredGroup) {
			return true;
		}

		if ($this->groupMode == self::GROUPMODE_REBIND) {
			try {
				$this->connection->bind($this->userDn, $this->password);
			} catch (\YapepBase\Exception\LdapBindException $e) {
				throw new \YapepBase\Exception\ParameterException('Can\'t bind to LDAP server with the given ' .
					'credentials.');
			}
		}

		if ($this->groupAttributeOnUser) {
			$base = $this->buildUserDn($username);
			if ($this->groupAttributeOnUserIsDn) {
				$param = (string)$this->buildGroupDn($this->requiredGroup);
			} else {
				$param = $this->requiredGroup;
			}
			$filter = $this->groupAttributeOnUser;
		} elseif ($this->userAttributeOnGroup) {
			$base = $this->buildGroupDn($this->requiredGroup);
			if ($this->userAttributeOnGroupIsDn) {
				$param = (string)$this->buildUserDn($username);
			} else {
				$param = $username;
			}
			$filter = $this->userAttributeOnGroup;
		} else {
			throw new \YapepBase\Exception\ParameterException('Neither group attribute for user object, not user ' .
				'attribute for group object are set, authorization can\'t be performed. Please use ' .
				'LdapAuthenticationProvider::setGroupAttributeOnUser() or ' .
				'LdapAuthenticationProvider::setUserAttributeOnGroup() before calling ' .
				'LdapAuthenticationProvider::authenticateAndAuthorize()');
		}

		$results = $this->connection->search(
			$base,
			$filter . '=:_param',
			array('param' => $param),
			array('dn'),
			LdapConnection::DEREF_NEVER,
			LdapConnection::SCOPE_SUB);

		// TODO: Not really an issue, but an empty() is enough here i think, and its much faster [emul]
		if (count($results)) {
			return true;
		} else {
			return false;
		}
	}
}
