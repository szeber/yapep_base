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
 * RouterException class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class RouterException extends Exception {
    const ERR_NO_ROUTE_FOUND = 101;
    const ERR_SYNTAX_PARAM = 201;
}