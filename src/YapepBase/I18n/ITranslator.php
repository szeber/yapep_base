<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   I18n
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\I18n;

/**
 * Interface for internationalization translators.
 *
 * @package    YapepBase
 * @subpackage I18n
 */
interface ITranslator {

	/**
	 * Translates the string.
	 *
	 * @param string $sourceClass   The source class (with the namespace).
	 * @param string $string        The string to translate.
	 * @param array  $params        Associative array with parameters for the translation. The key is the param name.
	 * @param string $language      The language for the translation. If not set, the default language will be used.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\I18n\DictionaryNotFoundException    If the dictionary is not found.
	 * @throws \YapepBase\Exception\I18n\TranslationNotFoundException   If the error mode is set to exception.
	 * @throws \YapepBase\Exception\I18n\ParameterException             If there are problems with the parameters.
	 */
	public function translate($sourceClass, $string, array $params = array(), $language = null);
}