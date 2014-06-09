<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;


use YapepBase\Exception\RouterException;

/**
 * Gets the route for the specified controller and action based on an associative array.
 *
 * The array's keys are in the Controller/Action format. The controller name should not include the namespace, and the
 * Action's name should not include the controller specific action prefix.
 * The array's values are the routes in the following format: [METHOD]URI
 * The method is optional (if present, the brackets are required). The URI may contain parameters in the format:
 * {paramName:paramType(options)}
 * The valid paramTypes are:
 *     <ul>
 *         <li>num: Any number. No options are allowed.</li>
 *         <li>alpha: Any alphabetic character. No options are allowed.</li>
 *         <li>alnum: Any alphanumeric character. No options are allowed.</li>
 *         <li>enum: Enumeration of the values in the options. The enumeration values should be separated by
 *                   the '|' character in the options string. Any '/' characters should be escaped.</li>
 *         <li>regex: Regular expression. The pattern should be included in the options string. The pattern should
 *                    not contain delimiters, and should be escaped for a '/' delimiter. It may not contain '{' or '}'
 *                    characters.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Router
 */
class LanguageArrayReverseRouter extends ArrayReverseRouter {

	/**
	 * The default language.
	 *
	 * @var string
	 */
	protected $defaultLanguage;

	/**
	 * The current language code.
	 *
	 * @var string
	 */
	protected $currentLanguage;

	/**
	 * Constructor
	 *
	 * @param array  $routes            The list of available routes
	 * @param string $currentLanguage   The code of the current language.
	 * @param string $defaultLanguage   The default language code to use.
	 */
	public function __construct(array $routes, $currentLanguage, $defaultLanguage) {
		parent::__construct($routes);

		$this->currentLanguage = $currentLanguage;
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * Returns the target (eg. URL) for the controller and action
	 *
	 * @param string $controller   The name of the controller
	 * @param string $action       The name of the action
	 * @param array  $params       Associative array with the route params, if they are required.
	 *
	 * @return string   The target.
	 *
	 * @throws RouterException   On errors. (Including if the route is not found)
	 */
	public function getTargetForControllerAction($controller, $action, $params = array()) {
		// We only put the language in the URI if it is not the default
		$languagePrefix = $this->currentLanguage == $this->defaultLanguage
			? ''
			: '/' . $this->currentLanguage;

		$uri = parent::getTargetForControllerAction($controller, $action, $params);
		if (empty($languagePrefix)) {
			return $uri;
		}
		else {
			return $languagePrefix . rtrim($uri, '/');
		}

	}
}