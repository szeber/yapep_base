<?php
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Janos Pasztor <net@janoszen.hu>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Exception;

/**
 * This exception indicates, that an index passed is invalid, because that
 * element doesn't exist.
 */
class IndexOutOfBoundsException extends \Exception {
    /**
     * Exception constructor
     *
     * @param integer|string|bool $offset the offset in question
     */
    function __construct($offset = false) {
        if ($offset !== false) {
            parent::__construct("Index out of bounds: " . $offset);
        } else {
            parent::__construct("Index out of bounds");
        }
    }
}
