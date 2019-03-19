<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase. It was merged from janoszen's Alternate-Class-Repository project.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception;

/**
 * This Exception states, that an invalid value was provided.
 */
class ValueException extends Exception
{
    /**
     * Constructor
     *
     * @param mixed  $value      The object which does not match the required type
     * @param string $required   The type required
     */
    public function __construct($value, $required = '')
    {
        $message = 'Invalid value: ' . $value;
        if ($required) {
            $message .= ' expected ' . $required;
        }
        parent::__construct($message);
    }
}
