<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\Debugger
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Debugger;

use YapepBase\Debugger\IDebugger;
use YapepBase\Debugger\Item\IDebugItem;

/**
 * Mock object for debugger testing
 *
 * @package    YapepBase
 * @subpackage Mock\Debugger
 */
class DebuggerMock implements IDebugger {

	/**
	 * Returns the time when the request was started as a float timestamp (microtime).
	 *
	 * @return float
	 */
	public function getStartTime() {
		// TODO: Implement getStartTime() method.
	}

	/**
	 * Adds a new debug item to the debugger.
	 *
	 * @param \YapepBase\Debugger\Item\IDebugItem $item   The debug item.
	 *
	 * @return void
	 */
	public function addItem(IDebugItem $item) {
		// TODO: Implement addItem() method.
	}

	/**
	 * Handles the shut down event.
	 *
	 * This method should called in case of shutdown(for example fatal error).
	 *
	 * @return mixed
	 */
	public function handleShutdown() {
		// TODO: Implement handleShutdown method [emul]
	}
}