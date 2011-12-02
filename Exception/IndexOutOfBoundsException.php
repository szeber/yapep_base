<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Exception;

/**
 * This exception indicates, that an index passed is invalid, because that
 * element doesn't exist.
 */
class IndexOutOfBoundsException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}