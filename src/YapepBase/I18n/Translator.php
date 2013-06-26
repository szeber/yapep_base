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

use YapepBase\Config;
use YapepBase\Storage\IStorage;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\I18n\ParameterException;
use YapepBase\Exception\I18n\DictionaryNotFoundException;
use YapepBase\Exception\I18n\TranslationNotFoundException;

/**
 * Internationalization class, that translates strings to other languages.
 *
 * May be used with either string IDs or natural language strings. The string ID method is recommended.
 *
 * The parameter names may contain both upper and lowercase alphanumeric, dash or underscore characters [-_a-zA-Z0-9].
 *
 * Configuration may be specified in the following format: <b>resource.i18n.translator.[configName].*</b>.
 *
 * The following configuration options may be used:
 * <ul>
 *     <li>paramPrefix: The prefix for the translation parameters. Defaults to '%'.</li>
 *     <li>paramSuffix: The suffix for the translation parameters. Defaults to '%'.</li>
 *     <li>
 *         strictParamChecking: Enables or disables strict parameter checking. Default to false,
 *         enabling it is only recommended for development. If enabled, the following checks will be performed:
 *         <ul>
 *             <li>All provided parameters are used in the string at least once.</li>
 *             <li>All parameters in the string are passed in.</li>
 *         </ul>
 *         If any of the above checks fail, a ParameterException will be thrown
 *        {@uses \YapepBase\Exception\I18n\ParameterException}.
 *     </li>
 *     <li>
 *         errorMode: Sets how the non-fatal translation errors are treated. The non-fatal errors include the
 *         translation not found error. Must be set to one of the ERROR_MODE_* constants. Defaults to exception
 *         {@uses self::ERROR_MODE_EXCEPTION}. The following error handling modes are available:
 *         <ul>
 *             <li>Throwing an exception. This is the default behavior. {@uses self::ERROR_MODE_EXCEPTION}</li>
 *             <li>Triggering an error of level E_USER_ERROR. {@uses self::ERROR_MODE_ERROR}</li>
 *             <li>Not treating this as an error condition. Useful if the translated strings are in one of the
 *                 languages.{@uses self::ERROR_MODE_NONE}</li>
 *         </ul>
 *         For all modes except for the exception mode, the original string will be returned with the parameters
 *         replaced in the source string.
 *     </li>
 *     <li>
 *         <b>errorModeForMissingDictionary</b>: Sets how to treat the error when the dictionary file is missing.
 *         Must be set to one of the ERROR_MODE_* constants. Defaults to exception {@uses self::ERROR_MODE_EXCEPTION}
 *         The effect is the same as at the errorMode param.
 *         This option should be only changed at the beginning of the development,
 *         as a missing dictionary should be treated very seriously.
 *     </li>
 * </ul>
 * Either the paramPrefix or paramSuffix is required to be non-empty (the defaults suffice), but it's recommended to
 * set both. If both paramPrefix and paramSuffix are set to empty string, or an invalid error mode is provided a
 * ConfigException will be thrown on instantiation {@uses \YapepBase\Exception\ConfigException}.
 *
 * @package    YapepBase
 * @subpackage I18n
 */
class Translator implements ITranslator {

	/** In case of non-fatal translation errors an exception will be thrown. */
	const ERROR_MODE_EXCEPTION = 'exception';

	/** In case of non-fatal translation errors an error of level E_USER_ERROR will be triggered. */
	const ERROR_MODE_ERROR = 'error';

	/** In case of non-fatal translation errors no error will be reported. */
	const ERROR_MODE_NONE = 'none';

	/**
	 * Cached dictionary file data
	 *
	 * @var array
	 */
	protected $dictionaryCache = array();

	/**
	 * The default language for the translations.
	 *
	 * @var string
	 */
	protected $defaultLanguage;

	/**
	 * The storage instance used to retrieve the dictionary files.
	 *
	 * @var \YapepBase\Storage\IStorage
	 */
	protected $storage;

	/**
	 * Stores whether strict param checking is enabled.
	 *
	 * @var bool
	 */
	protected $strictParamChecking = false;

	/**
	 * Prefix for the parameters.
	 *
	 * @var string
	 */
	protected $paramPrefix = '%';

	/**
	 * Suffix for the parameters.
	 *
	 * @var string
	 */
	protected $paramSuffix;

	/**
	 * The error mode to use.
	 *
	 * @var string
	 */
	protected $errorMode;

	/**
	 * The error mode to use when a dictionary is missing.
	 *
	 * @var string
	 */
	protected $errorModeForMissingDictionary;

	/**
	 * Constructor.
	 *
	 * @param \YapepBase\Storage\IStorage $storage           The storage instance used to retrieve the dictionaries.
	 * @param string                      $defaultLanguage   The default language to use for translations.
	 * @param string                      $configName        Name of the I18n configuration.
	 *
	 * @throws \YapepBase\Exception\ConfigException   On configuration problems.
	 */
	public function __construct(IStorage $storage, $defaultLanguage, $configName) {
		// Require a persistent storage
		if (!$storage->isPersistent()) {
			throw new ConfigException('Persistent storage is required for ' . __CLASS__);
		}

		$this->storage             = $storage;

		$this->setConfig($configName);
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * Sets up the object from the configuration.
	 *
	 * @param string $configName   Name of the I18n configuration.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException   On configuration problems.
	 */
	protected function setConfig($configName) {
		$config = Config::getInstance();
		$configBase = 'resource.i18n.translator.' . $configName;

		$this->paramPrefix         = (string)$config->get($configBase . '.paramPrefix', '%');
		$this->paramSuffix         = (string)$config->get($configBase . '.paramSuffix', '%');
		$this->strictParamChecking = (bool)$config->get($configBase . '.strictParamChecking', false);
		$this->errorMode           = (string)$config->get($configBase . '.errorMode', self::ERROR_MODE_EXCEPTION);
		$this->errorModeForMissingDictionary
			= (string)$config->get($configBase . '.errorModeForMissingDictionary', self::ERROR_MODE_EXCEPTION);

		if (
			!in_array($this->errorMode,
				array(self::ERROR_MODE_ERROR, self::ERROR_MODE_EXCEPTION, self::ERROR_MODE_NONE))
		) {
			throw new ConfigException('Invalid error mode for ' . __CLASS__ . ': ' . $this->errorMode);
		}

		if (empty($this->paramPrefix) && empty($this->paramSuffix)) {
			throw new ConfigException('Both the parameter prefix and suffix are empty');
		}
	}

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
	 * @throws \YapepBase\Exception\I18n\DictionaryNotFoundException    If the dictionary is not found in the storage.
	 * @throws \YapepBase\Exception\I18n\TranslationNotFoundException   If the error mode is set to exception.
	 * @throws \YapepBase\Exception\I18n\ParameterException             If there are problems with the parameters.
	 */
	public function translate($sourceClass, $string, array $params = array(), $language = null) {
		if (empty($language)) {
			$language = $this->defaultLanguage;
		}
		$sourceKey = $this->getSourceKey($sourceClass, $language);
		if (!isset($this->dictionaryCache[$sourceKey])) {
			$data = $this->storage->get($sourceKey);
			if (empty($data)) {
				$this->handleError($this->errorModeForMissingDictionary,
					new DictionaryNotFoundException('Dictionary not found for source class "' . $sourceClass
					. '" and language "' . $language . '"'));
			}
			$this->dictionaryCache[$sourceKey] = $data;
		}
		if (isset($this->dictionaryCache[$sourceKey][$string])) {
			$string = $this->dictionaryCache[$sourceKey][$string];
		} else {
			$this->handleError($this->errorMode,
				new TranslationNotFoundException('Translation not found for source class "' . $sourceClass
					. '", language "' . $language . '" and string: "' . $string . '"'));
		}
		return $this->substituteParameters($string, $params);
	}

	/**
	 * Handles the given error according to the given mode.
	 *
	 * @param string                         $errorMode   The error mode. {@uses self::ERROR_MODE_*}
	 * @param \YapepBase\Exception\Exception $exception   The exception what should be used.
	 *
	 * @throws Exception if the given mode is for exception.
	 *
	 * @return void
	 */
	protected function handleError($errorMode, Exception $exception) {
		switch ($errorMode) {
			case self::ERROR_MODE_NONE:
				// Do nothing
				break;

			case self::ERROR_MODE_ERROR:
				trigger_error($exception->getMessage(), E_USER_WARNING);
				break;

			case self::ERROR_MODE_EXCEPTION:
			default:
				throw $exception;
				break;
		}
	}

	/**
	 * Returns the key of the dictionary for the specified source class and language.
	 *
	 * @param string $sourceClass   The class name of the translation source.
	 * @param string $language      The language.
	 *
	 * @return string
	 */
	protected function getSourceKey($sourceClass, $language) {
		return $language . '-' . str_replace(array('\\', '/'), '-', $sourceClass);
	}

	/**
	 * Substitutes the parameter names with the values in the provided string
	 *
	 * @param string $string   The string to translate.
	 * @param array  $params   Associative array with parameters for the translation. The key is the param name.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\I18n\ParameterException
	 */
	protected function substituteParameters($string, array $params) {
		foreach ($params as $paramName => $paramValue) {
			$replacementCount = 0;
			$string = str_replace($this->paramPrefix . $paramName . $this->paramSuffix, $paramValue, $string,
				$replacementCount);
			if ($this->strictParamChecking && $replacementCount == 0) {
				throw new ParameterException('Parameter with name "' . $paramName . '" not found in provided string');
			}
		}
		if (
			$this->strictParamChecking
			&& preg_match('/' . preg_quote($this->paramPrefix, '/') . '[-_a-zA-Z0-9]+'
				. preg_quote($this->paramSuffix, '/') . '/', $string)
		) {
			throw new ParameterException('Not all parameters are set for the translated string');
		}
		return $string;
	}

	/**
	 * Sets the default language for the translator instance.
	 *
	 * @param string $language   The default language for the translations.
	 *
	 * @return void
	 */
	public function setDefaultLanguage($language) {
		$this->defaultLanguage = $language;
	}
}