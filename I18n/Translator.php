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

use YapepBase\Storage\IStorage;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ValueException;
use YapepBase\Exception\ParameterException;

/**
 * Internationalization class, that translates strings
 *
 * With strict parameter checking it checks the following things when translating:
 * <ul>
 *     <li>That all provided parameters are used in the string at least once.</li>
 *     <li>That all parameters in the string are passed in.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage I18n
 */
class Translator {

	/**
	 * Cached dictionary file data
	 *
	 * @var array
	 */
	protected $dictionaryCache = array();

	/**
	 * The default language for the translations
	 *
	 * @var string
	 */
	protected $defaultLanguage;

	/**
	 * The storage instance used to retrieve the dictionary files
	 *
	 * @var \YapepBase\Storage\IStorage
	 */
	protected $storage;

	/**
	 * Stores whether strict param checking is enabled.
	 *
	 * @var bool
	 */
	protected $strictParamChecking;

	/**
	 * Constructor.
	 *
	 * If strict parameter checking opt
	 *
	 * @param \YapepBase\Storage\IStorage $storage               The storage instance used to retrieve the dictionaries.
	 * @param string                      $defaultLanguage       The default language to use for translations.
	 * @param bool                        $strictParamChecking   If TRUE enables strict parameter checking.
	 */
	public function __construct(IStorage $storage, $defaultLanguage, $strictParamChecking = false) {
		$this->storage             = $storage;
		$this->strictParamChecking = $strictParamChecking;

		$this->setDefaultLanguage($defaultLanguage);
	}

	/**
	 * Sets the default language.
	 *
	 * @param string $defaultLanguage    The default language.
	 */
	public function setDefaultLanguage($defaultLanguage) {
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * Returns the default language.
	 *
	 * @return mixed
	 */
	public function getDefaultLanguage() {
		return $this->defaultLanguage;
	}

	/**
	 * Translates the string.
	 *
	 * @param string $sourceClass   The source class (with the namespace).
	 * @param string $string        The string to translate.
	 * @param array  $params        Parameters for the translation.
	 * @param string $language      The language for the translation. If not set, the default language will be used.
	 *
	 * @return string
	 */
	public function translate($sourceClass, $string, array $params = array(), $language = null) {
		if (empty($language)) {
			$language = $this->defaultLanguage;
		}
		$sourceKey = $this->getSourceKey($sourceClass, $language);
		if (!isset($this->dictionaryCache[$sourceKey])) {
			$data = $this->storage->get($sourceKey);
			if (empty($data)) {
				throw new Exception('Dictionary not found for source class "' . $sourceClass . '" and language "'
					. $language . '"');
			}
			$this->dictionaryCache[$sourceKey] = $data;
		}
		if (!isset($this->dictionaryCache[$sourceKey][$string])) {
			throw new ValueException('Translation not found for source class "' . $sourceClass . '", language "'
				. $language . '" and string: "' . $string . '"');
		}
		return $this->substituteParameters($string, $params);
	}

	/**
	 * Alias to the translate() method.
	 *
	 * @param string $sourceClass   The source class (with the namespace).
	 * @param string $string        The string to translate.
	 * @param array  $params        Parameters for the translation.
	 * @param string $language      The language for the translation. If not set, the default language will be used.
	 *
	 * @return string
	 *
	 * @see self::translate()
	 */
	public function _($sourceClass, $string, array $params = array(), $language = null) {
		return $this->translate($sourceClass, $string, $params, $language);
	}

	protected function getSourceKey($sourceClass, $language) {
		return $language . '-' . str_replace(array('\\', '/'), '-', $sourceClass);
	}

	protected function substituteParameters($string, array $params) {
		foreach ($params as $paramName => $paramValue) {
			$replacementCount = 0;
			$string = str_replace('%' . $paramName . '%', $paramValue, $string, $replacementCount);
			if ($this->strictParamChecking && $replacementCount == 0) {
				throw new ParameterException('Parameter with name "' . $paramName . '" not found in provided string');
			}
		}
		if ($this->strictParamChecking && preg_match('/%[-_a-zA-Z0-9]%/', $string)) {
			throw new ParameterException('Not all parameters are set for the translated string');
		}
		return $string;
	}
}