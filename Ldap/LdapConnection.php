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

use YapepBase\Exception\LdapAddException;
use YapepBase\Exception\LdapBindException;
use YapepBase\Exception\LdapConnectionException;
use YapepBase\Exception\LdapDeleteException;
use YapepBase\Exception\LdapModifyException;

/**
 * This is a single LDAP connection. It supports only LDAPv3 servers.
 */
class LdapConnection {
	/**
	 * The connection link. 
	 */
	protected $link;
	/**
	 * Never dereference aliases.
	 */
	const DEREF_NEVER = LDAP_DEREF_NEVER;
	/**
	 * Aliases should be dereferenced during the search but not when locating the base object of the search.  
	 */
	const DEREF_SEARCH = LDAP_DEREF_SEARCHING;
	/**
	 * Aliases should be dereferenced when locating the base object but not during the search.  
	 */
	const DEREF_FIND = LDAP_DEREF_FINDING;
	/**
	 * Aliases should be dereferenced always.  
	 */
	const DEREF_ALWAYS = LDAP_DEREF_ALWAYS;
	/**
	 * Searches the LDAP with the subtree option.
	 */
	const SCOPE_SUB = 1;
	/**
	 * Searches one level of the LDAP tree. 
	 */
	const SCOPE_ONE = 0;

	/**
	 * If the function has parameters, connects to the LDAP server.
	 * @param   string   $hostname
	 * @param   int      $port
	 */
	public function __construct($hostname = false, $port = false) {
		if ($hostname) {
			$this->connect($hostname, $port);
		}
	}

	/**
	 * Closes the link, 
	 */
	public function __destruct() {
		if ($this->link) {
			$this->disconnect();
		}
	}

	/**
	 * Explicitly connects the LDAP server 
	 * @param   string   $hostname   optional
	 * @param   int      $port       optional
	 */
	public function connect($hostname = false, $port = false) {
		if ($hostname && $port) {
			$link = ldap_connect($hostname, $port);
		} else if ($hostname) {
			$link = ldap_connect($hostname);
		} else {
			$link = ldap_connect();
		}

		if (!$link) {
			throw new LdapConnectionException($link);
		}

		$this->link = $link;
		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
	}

	/**
	 * Disconnects from the LDAP server. 
	 */
	public function disconnect() {
		ldap_close($this->link);
		$this->link = null;
	}

	/**
	 * Binds (authenticates) with the LDAP server. Pass empty parameters to do an anonymous bind.
	 * @param   LdapDn   $rdn        optional
	 * @param   string   $password   optional
	 */
	public function bind(LdapDn $rdn = null, $password = "") {
		if (!$this->link) {
			$this->connect();
		}

		if ($rdn) {
			if ($password)
			{
				$bind = ldap_bind($this->link, (string)$rdn, $password);
			} else {
				$bind = ldap_bind($this->link, (string)$rdn);
			}

			if (!$bind) {
				throw new LdapBindException($this->link);
			}
		} else {
			$bind = ldap_bind();
			if (!$bind) {
				throw new LdapBindException($this->link);
			}
		}
	}

	/**
	 * Unbinds (deauthenticates) from an LDAP server. Useful, if you want to change users within a connection. 
	 */
	public function unbind() {
		if (!ldap_unbind($this->link)) {
			throw new LdapBindException($this->link);
		}
	}

	/**
	 * Runs an LDAP add operation on the server.
	 * @param   LdapDn      $dn     the distinguised name of the entry.
	 * @param   LdapEntry   $enrty  the data in the entry.
	 * @throws  \YapepBase\Exception\LdapAddException   if the add fails
	 */
	public function add(LdapDn $dn, LdapEntry $entry) {
		if (!$this->link) {
			$this->connect();
		}

		$result = ldap_add($this->link, (string)$dn, $entry->getAsArray());

		if (!$result) {
			throw new LdapAddException($this->link);
		}
	}

	/**
	 * Runs an LDAP modify operation on the server.
	 * @param   LdapDn      $dn     the distinguised name of the entry.
	 * @param   LdapEntry   $enrty  the data in the entry.
	 * @throws  \YapepBase\Exception\LdapModifyException   if the modify fails
	 */
	function modify(LdapDn $dn, LdapEntry $entry)
	{
		if (!$this->link) {
			$this->connect();
		}

		$result = ldap_modify($this->link, (string)$dn, $entry->getAsArray());

		if (!$result) {
			throw new LdapModifyException($this->link);
		}
	}

	/**
	 * Deletes an entry from the LDAP.
	 * @param   LdapDn   $dn
	 * @throws  \YapepBase\Exception\LdapDeleteException   if the deletion fails.
	 */
	function delete(LdapDn $dn) {
		if (!$this->link) {
			$this->connect();
		}

		$result = ldap_delete($this->link, (string)$dn);

		if (!$result) {
			throw new LdapDeleteException($this->link);
		}
	}
	
	/**
	 * Internal function to decode a hexadecimal character. 
	 */
	protected function decodeHex($value) {
	    return chr(hexdec($value));
	}
	
	/**
	 * Internal function to decode a hex value.
	 * @param   string  $value
	 * @return  string
	 */
	protected function decode($value) {
	    return preg_replace("/(\x([0-9A-Fa-f][0-9A-Fa-f]))/e", "\$this->decodeHex('\\2')", $value);
	}
	
	/**
	 * Postprocesses an LDAP entry. (Decodes hex characters, etc.)
	 * @param   array|string   $data 
	 * @return  array|string
	 */
	protected function postprocess($data) {
	    if (is_array($data)) {
			if (isset($data['count'])) {
				unset($data['count']);
			}
			foreach ($data as $key => $value) {
				$data[$key] = $this->postprocess($value);
			}
	    } else {
			$data = $this->decode($data);
	    }
	    return $data;
	}
	
	/**
	 * Runs a search operation on the LDAP server and returns the result.
	 * @param   LdapDn   $rootdn
	 * @param   string   $filter         The filter with params encoded as :_placeholder.
	 * @param   array    $filterparams
	 * @param   array    $attributes     Attributes to request. Optional.
	 * @param   int      $deref          Dereference options. Use self::DEREF_*
	 * @param   int      $scope          Scoping options. Use self::SCOPE_*
	 * @return  array
	 */
	public function search(LdapDn $rootdn, $filter, $filterparams = array(), $attributes = array(), $deref = self::DEREF_NEVER, $scope = self::SCOPE_SUB) {
	    if (!$this->link) {
			$this->connect();
	    }
	    foreach ($filterparams as $key => $value) {
			unset($filterparams[$key]);
			$filterparams[":_" . $key] = $value;
	    }
	    if ($scope == self::SCOPE_SUB) {
			$result = ldap_search($this->link, (string)$rootdn, strtr($filter, $filterparams), $attributes);
	    } else {
			$result = ldap_list($this->link, (string)$rootdn, strtr($filter, $filterparams), $attributes);
	    }
		$result = ldap_get_entries($this->link, $result);
		$result = $this->postprocess($result);
		if ($result === false) {
			throw new LdapSearchException($this->link);
		}
		return $result;
	}
}
