<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\ErrorHandler;


/**
 * Interface for terminatable objects.
 *
 * Classes implementing this interface may be registered as terminators to the ErrorHandlerRegistry, and the
 * terminate() method will be called last before the application ends.
 *
 * The terminate() method is usable to cleanly exit and also return an exit code.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
interface ITerminatable {

	/**
	 * Called just before the application exits.
	 *
	 * @param bool $isFatalError   TRUE if the termination is because of a fatal error.
	 *
	 * @return void
	 */
	public function terminate($isFatalError);
}
