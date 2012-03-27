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
 * ViewException class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class ViewException extends Exception {
	/** Indicates, that a block was not found. */
	const ERR_BLOCK_NOT_FOUND = 101;
	/** Indicates, that a template was not found. */
	const ERR_TEMPLATE_NOT_FOUND = 102;
	/** Indicates, that a layout was not found. */
	const ERR_LAYOUT_NOT_FOUND = 103;
}