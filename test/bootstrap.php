<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

use YapepBase\Autoloader\SimpleAutoloader;
use YapepBase\Autoloader\AutoloaderRegistry;

include_once(__DIR__ . '/../bootstrap.php');

define('TEST_DIR', __DIR__);

\YapepBase\Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()
	->addErrorHandler(new \YapepBase\ErrorHandler\ExceptionCreatorErrorHandler());

$autoloadDirs = require realpath(__DIR__ . '/../vendor/composer/autoload_namespaces.php');

// Autoloader setup
$autoloader = new SimpleAutoloader();
if (defined('APP_ROOT')) {
	$autoloader->addClassPath(APP_ROOT . '/class');
}
$autoloader->addClassPath(TEST_DIR);
foreach ($autoloadDirs as $dir) {
	$autoloader->addClassPath($dir);
}
AutoloaderRegistry::getInstance()->addAutoloader($autoloader);

unset($autoloader);