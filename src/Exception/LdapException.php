<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception;

/**
 * This is the generic LDAP exception
 *
 * Error codes courtesy of https://wiki.servicenow.com/index.php?title=LDAP_Error_Codes
 */
class LdapException extends \Exception
{
    /** Indicates the requested client operation completed successfully. */
    const LDAP_SUCCESS = 0;

    /**
     * Indicates an internal error. The server is unable to respond with a more specific error and is also unable to
     * properly respond to a request. It does not indicate that the client has sent an erroneous message. In NDS 8.3x
     * through NDS 7.xx, this was the default error for NDS errors that did not map to an LDAP error code. To conform
     * to the new LDAP drafts, NDS 8.5 uses 80 (0x50) for such errors.
     */
    const LDAP_OPERATIONS_ERROR = 1;

    /** Indicates that the server has received an invalid or malformed request from the client. */
    const LDAP_PROTOCOL_ERROR = 2;

    /**
     * Indicates that the operation's time limit specified by either the client or the server has been exceeded.
     * On search operations, incomplete results are returned.
     */
    const LDAP_TIMELIMIT_EXCEEDED = 3;

    /**
     * Indicates that in a search operation, the size limit specified by the client or the server has been exceeded.
     * Incomplete results are returned.
     */
    const LDAP_SIZELIMIT_EXCEEDED = 4;

    /** Does not indicate an error condition. Indicates that the results of a compare operation are false. */
    const LDAP_COMPARE_FALSE = 5;

    /** Does not indicate an error condition. Indicates that the results of a compare operation are true. */
    const LDAP_COMPARE_TRUE = 6;

    /**
     * Indicates that during a bind operation the client requested an authentication method not supported by the
     * LDAP server.
     */
    const LDAP_AUTH_METHOD_NOT_SUPPORTED = 7;

    /**
     * Indicates one of the following: In bind requests, the LDAP server accepts only strong authentication.
     * In a client request, the client requested an operation such as delete that requires strong authentication.
     * In an unsolicited notice of disconnection, the LDAP server discovers the security protecting the communication
     * between the client and server has unexpectedly failed or been compromised.
     */
    const LDAP_STRONG_AUTH_REQUIRED = 8;

    /**
     * Does not indicate an error condition. In LDAPv3, indicates that the server does not hold the target entry of the
     * request, but that the servers in the referral field may.
     */
    const LDAP_REFERRAL = 10;

    /** Indicates that an LDAP server limit set by an administrative authority has been exceeded. */
    const LDAP_ADMINLIMIT_EXCEEDED = 11;

    /**
     * Indicates that the LDAP server was unable to satisfy a request because one or more critical extensions were
     * not available. Either the server does not support the control or the control is not appropriate for
     * the operation type.
     */
    const LDAP_UNAVAILABLE_CRITICAL_EXTENSION = 12;

    /**
     * Indicates that the session is not protected by a protocol such as Transport Layer Security (TLS), which provides
     * session confidentiality.
     */
    const LDAP_CONFIDENTIALITY_REQUIRED = 13;

    /**
     * Does not indicate an error condition, but indicates that the server is ready for the next step in the process.
     * The client must send the server the same SASL mechanism to continue the process.
     */
    const LDAP_SASL_BIND_IN_PROGRESS = 14;

    /** Indicates that the attribute specified in the modify or compare operation does not exist in the entry. */
    const LDAP_NO_SUCH_ATTRIBUTE = 16;

    /**
     * Indicates that the attribute specified in the modify or add operation does not exist in the
     * LDAP server's schema.
     */
    const LDAP_UNDEFINED_TYPE = 17;

    /**
     * Indicates that the matching rule specified in the search filter does not match a rule defined for
     * the attribute's syntax.
     */
    const LDAP_INAPPROPRIATE_MATCHING = 18;

    /**
     * Indicates that the attribute value specified in a modify, add, or modify DN operation violates constraints
     * placed on the attribute. The constraint can be one of size or content (string only, no binary).
     */
    const LDAP_CONSTRAINT_VIOLATION = 19;

    /**
     * Indicates that the attribute value specified in a modify or add operation already exists as a value
     * for that attribute.
     */
    const LDAP_TYPE_OR_VALUE_EXISTS = 20;

    /**
     * Indicates that the attribute value specified in an add, compare, or modify operation is an unrecognized
     * or invalid syntax for the attribute.
     */
    const LDAP_INVALID_SYNTAX = 21;

    /**
     * Indicates the target object cannot be found. This code is not returned on following operations:
     * Search operations that find the search base but cannot find any entries that match the search filter.
     * Bind operations.
     */
    const LDAP_NO_SUCH_OBJECT = 32;

    /** Indicates that an error occurred when an alias was dereferenced. */
    const LDAP_ALIAS_PROBLEM = 33;

    /**
     * Indicates that the syntax of the DN is incorrect. (If the DN syntax is correct, but the LDAP server's
     * structure rules do not permit the operation, the server returns LDAP_UNWILLING_TO_PERFORM.)
     */
    const LDAP_INVALID_DN_SYNTAX = 34;

    /**
     * Indicates that the specified operation cannot be performed on a leaf entry. (This code is not currently
     * in the LDAP specifications, but is reserved for this constant.)
     */
    const LDAP_IS_LEAF = 35;

    /**
     * Indicates that during a search operation, either the client does not have access rights to read the
     * aliased object's name or dereferencing is not allowed.
     */
    const LDAP_ALIAS_DEREF_PROBLEM = 36;

    /**
     * Indicates that during a bind operation, the client is attempting to use an authentication method that
     * the client cannot use correctly. For example, either of the following cause this error: The client returns
     * simple credentials when strong credentials are required...OR...The client returns a DN and a password for a
     * simple bind when the entry does not have a password defined.
     */
    const LDAP_INAPPROPRIATE_AUTH = 48;

    /**
     * Indicates that during a bind operation one of the following occurred: The client passed either an incorrect
     * DN or password, or the password is incorrect because it has expired, intruder detection has locked the account,
     * or another similar reason. This is equivalent to AD error code 52e.
     */
    const LDAP_INVALID_CREDENTIALS = 49;

    /**
     * Corresponds to data code 568. Indicates that during a log-on attempt, the user's security context accumulated
     * too many security IDs. This is an issue with the specific LDAP user object/account which should be investigated
     * by the LDAP administrator.
     */
    const ERROR_TOO_MANY_CONTEXT_IDS = 49;

    /** Indicates that the caller does not have sufficient rights to perform the requested operation. */
    const LDAP_INSUFFICIENT_ACCESS = 50;

    /**
     * Indicates that the LDAP server is too busy to process the client request at this time but if the client waits
     * and resubmits the request, the server may be able to process it then.
     */
    const LDAP_BUSY = 51;

    /** Indicates that the LDAP server cannot process the client's bind request, usually because it is shutting down. */
    const LDAP_UNAVAILABLE = 52;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext error, which is returned when the username is valid but
     * the combination of password and user credential is invalid.This is the AD equivalent of LDAP error code 49.
     */
    const AD_INVALID_CREDENTIALS = '52e';

    /**
     * Indicates that the LDAP server cannot process the request because of server-defined restrictions.
     * This error is returned for the following reasons: The add entry request violates the server's
     * structure rules...OR...The modify attribute request specifies attributes that users
     * cannot modify...OR...Password restrictions prevent the action...OR...Connection restrictions prevent the
     * action.
     */
    const LDAP_UNWILLING_TO_PERFORM = 53;

    /** Indicates that the client discovered an alias or referral loop, and is thus unable to complete this request. */
    const LDAP_LOOP_DETECT = 54;

    /**
     * Indicates that the add or modify DN operation violates the schema's structure rules. For example, the request
     * places the entry subordinate to an alias. The request places the entry subordinate to a container that is
     * forbidden by the containment rules. The RDN for the entry uses a forbidden attribute type.
     */
    const LDAP_NAMING_VIOLATION = 64;

    /**
     * Indicates that the add, modify, or modify DN operation violates the object class rules for the entry.
     * For example, the following types of request return this error: The add or modify operation tries to add an
     * entry without a value for a required attribute. The add or modify operation tries to add an entry with a value
     * for an attribute which the class definition does not contain. The modify operation tries to remove a required
     * attribute without removing the auxiliary class that defines the attribute as required.
     */
    const LDAP_OBJECT_CLASS_VIOLATION = 65;

    /**
     * Indicates that the requested operation is permitted only on leaf entries. For example, the following types of
     * requests return this error: The client requests a delete operation on a parent entry. The client request a
     * modify DN operation on a parent entry.
     */
    const LDAP_NOT_ALLOWED_ON_NONLEAF = 66;

    /**
     * Indicates that the modify operation attempted to remove an attribute value that forms the entry's
     * relative distinguished name.
     */
    const LDAP_NOT_ALLOWED_ON_RDN = 67;

    /**
     * Indicates that the add operation attempted to add an entry that already exists, or that the modify operation
     * attempted to rename an entry to the name of an entry that already exists.
     */
    const LDAP_ALREADY_EXISTS = 68;

    /** Indicates that the modify operation attempted to modify the structure rules of an object class. */
    const LDAP_NO_OBJECT_CLASS_MODS = 69;

    /** Reserved for CLDAP. */
    const LDAP_RESULTS_TOO_LARGE = 70;

    /**
     * Indicates that the modify DN operation moves the entry from one LDAP server to another and requires more than
     * one LDAP server.
     */
    const LDAP_AFFECTS_MULTIPLE_DSAS = 71;

    /**
     * Indicates an unknown error condition. This is the default value for NDS error codes which do not map to
     * other LDAP error codes.
     */
    const LDAP_OTHER = 80;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is returned when the username is
     * invalid.
     */
    const AD_USER_NOT_FOUND = 525;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is logon failure caused because the
     * user is not permitted to log on at this time. Returns only when presented with a valid username and valid
     * password credential.
     */
    const NOT_PERMITTED_TO_LOGON_AT_THIS_TIME = 530;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is logon failure caused because the
     * user is not permitted to log on from this computer. Returns only when presented with a valid username and valid
     * password credential.
     */
    const RESTRICTED_TO_SPECIFIC_MACHINES = 531;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The specified
     * account password has expired. Returns only when presented with valid username and password credential.
     */
    const PASSWORD_EXPIRED = 532;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The account is
     * currently disabled. Returns only when presented with valid username and password credential.
     */
    const ACCOUNT_DISABLED = 533;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error that is a logon failure. The user's account
     * has expired. Returns only when presented with valid username and password credential.
     */
    const ACCOUNT_EXPIRED = 701;

    /**
     * Indicates an Active Directory (AD) AcceptSecurityContext data error. The user's password must be changed before
     * logging on the first time. Returns only when presented with valid user-name and password credential.
     */
    const USER_MUST_RESET_PASSWORD = 773;

    /**
     * Extracts the error message and code from the connection
     *
     * @param resource $link   The link resource
     */
    public function __construct($link)
    {
        parent::__construct(ldap_error($link), ldap_errno($link));
    }
}
