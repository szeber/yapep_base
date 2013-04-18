<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package   YapepBase
 * @copyright 2011 The YAPEP Project All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase;

if (!\defined('BASE_DIR')) {
	/** The base directory */
	define('BASE_DIR', realpath(dirname((__FILE__))) . '/src');
}

/** Require the simple autoloader */
require_once BASE_DIR . '/YapepBase/Autoloader/SimpleAutoloader.php';
require_once BASE_DIR . '/YapepBase/Autoloader/AutoloaderRegistry.php';

$autoloader = new \YapepBase\Autoloader\SimpleAutoloader();
$autoloader->addClassPath(BASE_DIR);
\YapepBase\Autoloader\AutoloaderRegistry::getInstance()->addAutoloader($autoloader);

unset($autoloader);

