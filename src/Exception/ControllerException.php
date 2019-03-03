<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Exception;

/**
 * ControllerException class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class ControllerException extends Exception {

	// Controller compatibility errors
	/** The received request object is not compatible with the controller. */
	const ERR_INCOMPATIBLE_REQUEST = 101;
	/** The received response object is not compatible with the controller. */
	const ERR_INCOMPATIBLE_RESPONSE = 102;
	/** The value returned by the action is of a non-supported type. */
	const ERR_INVALID_ACTION_RETURN_VALUE = 103;

	// Action related errors
	/** The return value received from the action is invalid. */
	const ERR_INVALID_ACTION_RESULT = 201;
	/** The action is not present in the controller. */
	const ERR_ACTION_NOT_FOUND = 202;
	/** The controller is not found in the system. */
	const ERR_CONTROLLER_NOT_FOUND = 203;
}