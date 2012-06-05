<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Batch;

use YapepBase\Batch\BatchScript;

/**
 * Mock class for testing the BatchScript class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Batch
 */
class BatchScriptMock extends BatchScript {

	/**
	 * Data storage for the closure.
	 *
	 * @var mixed
	 */
	static public $closureData;

	/**
	 * The closure that's called from the execute method.
	 *
	 * @var \Closure
	 */
	static public $executeClosure;

	/**
	 * Executes the script.
	 *
	 * @return void
	 */
	protected function execute() {
		static::$closureData = null;
		$closure = static::$executeClosure;
		$closure($this);
		static::$executeClosure = null;
	}

	public function setToView($nameOrData, $value = null) {
		parent::setToView($nameOrData, $value);
	}

}