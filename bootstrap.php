<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase;

if (!\defined('BASE_DIR')) {
	/** The base directory */
	define('BASE_DIR', realpath(dirname((__FILE__))) . '/src');
}

if (!\defined('VENDOR_DIR')) {
	/** The vendor directory */
	define('VENDOR_DIR', dirname(BASE_DIR) . '/vendor');
}

/** Require the vendor autoloader */
require_once VENDOR_DIR . '/autoload.php';

/** Require the simple autoloader */
require_once BASE_DIR . '/YapepBase/Autoloader/AutoloaderBase.php';
require_once BASE_DIR . '/YapepBase/Autoloader/SimpleAutoloader.php';
require_once BASE_DIR . '/YapepBase/Autoloader/AutoloaderRegistry.php';
$autoloader = new \YapepBase\Autoloader\SimpleAutoloader();
$autoloader->setClassPath(array(BASE_DIR));
$autoloader->register();


unset($autoloader);
