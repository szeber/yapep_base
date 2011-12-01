<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
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

    // Action related errors
    /** The return value received from the action is invalid. */
    const ERR_INVALID_ACTION_RETURN_VALUE = 201;
}