<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Helper;

/**
 * File related helper functions.
 */
class FileHelper
{
    /**
     * Returns the environment of the given line from the given file.
     *
     * Can be really useful when looking for the context of an error.
     *
     * @param string $file    The path of the file.
     * @param int    $line    The number of the asked row.
     * @param int    $range   The quantity of the lines before and after the given one (the radius of the environment).
     *
     * @return array   The asked row and its environment in an array. The key is the ordinal number of the row,
     *                 and the value is the line itself.
     */
    public static function getEnvironment($file, $line, $range)
    {
        $result = [];
        $buffer = file($file, FILE_IGNORE_NEW_LINES);
        if ($buffer !== false) {
            // We're shifting the ordinal number by one to make it the same as the line number
            array_unshift($buffer, null);
            unset($buffer[0]);
            $result = array_slice($buffer, max(0, $line - $range - 1), 2 * $range + 1, true);
        }

        return $result;
    }
}
