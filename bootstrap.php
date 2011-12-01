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

/** The base directory */
define('BASE_DIR', realpath(dirname(dirname(__FILE__))) . '/');

/** Require the simple autoloader */
require_once BASE_DIR . 'YapepBase/Autoloader/SimpleAutoloader.php';
\YapepBase\Autoloader\SimpleAutoloader::register();