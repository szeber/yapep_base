<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

include_once(__DIR__ . '/../bootstrap.php');

define('TEST_DIR', __DIR__);

\YapepBase\Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()
	->addErrorHandler(new \YapepBase\ErrorHandler\StrictErrorHandler());

$autoloader = new \YapepBase\Autoloader\SimpleAutoloader();
$autoloader->addClassPath(TEST_DIR);
// Find vfsStream, and register autoloading for it, if available
foreach(explode(\PATH_SEPARATOR, get_include_path()) as $path) {
	if (file_exists($path . \DIRECTORY_SEPARATOR . 'vfsStream')) {
		$autoloader->addClassPath($path . \DIRECTORY_SEPARATOR . 'vfsStream');
		break;
	}
}

$autoloader->register();
unset($autoloader, $path);
