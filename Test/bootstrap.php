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
\YapepBase\Application::getInstance()->getErrorHandlerContainer()->addErrorHandler(new \YapepBase\ErrorHandler\StrictErrorHandler());

// Find vfsStream, and register autoloading for it, if available
foreach(explode(\PATH_SEPARATOR, get_include_path()) as $path) {
    if (file_exists($path . \DIRECTORY_SEPARATOR . 'vfsStream')) {
        $autoloader = new \YapepBase\Autoloader\SimpleAutoloader();
        $autoloader->setClassPath(array($path . \DIRECTORY_SEPARATOR . 'vfsStream'));
        $autoloader->register();
        unset($autoloader);
        break;
    }
}

unset($path);
