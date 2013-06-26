<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Router
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;
use YapepBase\Exception\ParameterException;
use YapepBase\Request\IRequest;

/**
 * Adds the ability to have translated routes to the LanguageArrayRouter.
 *
 * @inheritdoc
 *
 * The routes array must be a 2 dimensional array, where the first dimension's key is the language code, the value is
 * the route array for that language.
 *
 * @package    YapepBase
 * @subpackage Router
 */
class TranslatedLanguageArrayRouter extends LanguageArrayRouter implements ILanguageReverseRouter {

	/**
	 * The reverse router.
	 *
	 * @var ILanguageReverseRouter
	 */
	protected $reverseRouter;


	/**
	 * Constructor
	 *
	 * @param \YapepBase\Request\IRequest $request               The request instance.
	 * @param array                       $routes                The list of available routes.
	 * @param string                      $defaultLanguage       The default language code to use.
	 * @param array                       $usableLanguages       Contains the usable languages.
	 * @param array                       $notTranslatedRoutes   The list of available routes that are not translated.
	 * @param ILanguageReverseRouter      $reverseRouter         The reverse router to use.
	 *
	 * @throws \YapepBase\Exception\ParameterException
	 */
	public function __construct(
		IRequest $request, array $routes, $defaultLanguage, $usableLanguages, array $notTranslatedRoutes = array(),
		ILanguageReverseRouter $reverseRouter = null
	) {
		// Add the not translated routes to the translated routes, and ensure that all languages have routes
		foreach ($usableLanguages as $languageCode) {
			if (!isset($routes[$languageCode])) {
				throw new ParameterException('Language has no translated routes: ' . $languageCode);
			}
			$routes[$languageCode] = array_merge($notTranslatedRoutes, $routes[$languageCode]);
		}
		parent::__construct($request, $routes, $defaultLanguage, $usableLanguages,
			empty($reverseRouter)
				? new TranslatedLanguageArrayReverseRouter($routes,
					$this->getCurrentLanguageFromRequest($request, $usableLanguages, $defaultLanguage),
					$defaultLanguage)
				: $reverseRouter
		);
	}

	/**
	 * Returns the array of routes.
	 *
	 * @return array
	 */
	public function getRouteArray() {
		return $this->routes[$this->currentLanguage];
	}

	/**
	 * Returns the target (eg. URL) for the controller and action in the specified language.
	 *
	 * @param string $controller   The name of the controller.
	 * @param string $action       The name of the action.
	 * @param array  $language     Code of the language to use to return the target.
	 * @param array  $params       Associative array with the route params, if they are required.
	 *
	 * @return string   The target.
	 *
	 */
	public function getTargetForControllerActionInLanguage($controller, $action, $language, $params = array()) {
		return $this->reverseRouter->getTargetForControllerActionInLanguage($controller, $action, $language, $params);
	}

}
