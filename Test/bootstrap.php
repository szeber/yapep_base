<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

include_once(__DIR__ . '/../bootstrap.php');

/**
 * Load all classes, so we have real code coverage.
 */
function loadDirectory($path) {
    if (($dh = opendir($path)) !== false) {
        while (($entry = readdir($dh)) !== false) {
            if (!preg_match('/(^Test$|Test\.php$|^\.$|^\.\.$)/', $entry)) {
                $newpath = $path . '/' . $entry;
                if (is_dir($newpath)) {
                    loadDirectory($newpath);
                } else if (is_file($newpath) && preg_match('/\.php/', $newpath)) {
                    include_once($newpath);
                }
            }
        }
    }
}

loadDirectory(__DIR__ . '/../');