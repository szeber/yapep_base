<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\Helper
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Helper;

use YapepBase\Helper\HelperAbstract;

/**
 * Mock class for helpers
 *
 * @package    YapepBase
 * @subpackage Mock\Helper
 */
class HelperMock extends HelperAbstract {

	public function _($string, $parameters = array(), $language = null) {
		return parent::_($string, $parameters, $language);
	}

}