<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock;

use YapepBase\Config;

/**
 * Mock class for the Application.
 *
 * @codeCoverageIgnore
 *
 * @package    YapepBase
 * @subpackage Mock
 */
class ApplicationMock extends \YapepBase\Application {

	/**
	 * Constructor.
	 *
	 * We have to override the Application__construct because of the ErrorHandler.
	 */
	public function __construct() {
		$this->config = Config::getInstance();
	}
}