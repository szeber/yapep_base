<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Autoloader;


/**
 * Interface for loading classes automatically.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
interface IAutoloader {
	/**
	 * Loads the specified class if it can be found by name.
	 *
	 * @param string $className   Name of the class.
	 *
	 * @return bool   TRUE if the class was loaded, FALSE if it can't be loaded.
	 */
	public function load($className);
}