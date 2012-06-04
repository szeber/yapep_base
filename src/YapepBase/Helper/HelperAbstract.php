<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Helper
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;
use YapepBase\Application;

/**
 * Abstract base class for helpers
 *
 * @package    YapepBase
 * @subpackage Helper
 */
abstract class HelperAbstract {

	/**
	 * Translates the specified string.
	 *
	 * @param string $string       The string.
	 * @param array  $parameters   The parameters for the translation.
	 * @param string $language     The language.
	 *
	 * @return string
	 */
	protected function _($string, $parameters = array(), $language = null) {
		return Application::getInstance()->getI18nTranslator()->translate(__CLASS__, $string, $parameters, $language);
	}

}