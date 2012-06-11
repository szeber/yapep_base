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
 * DiException class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class DiException extends Exception {

	/** Error code for class not found error during namespace search. */
	const ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND = 101;

	/** Error code for parameter not set error. */
	const ERR_PARAMETER_NOT_SET = 201;

	/** Error code for instance not set error. */
	const ERR_INSTANCE_NOT_SET = 202;

}