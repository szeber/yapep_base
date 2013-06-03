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


use YapepBase\Request\IRequest;

/**
 * Adds the ability of language handling to the Array Router.
 *
 * @package    YapepBase
 * @subpackage Router
 */
class LanguageArrayRouter extends ArrayRouter {

	/**
	 * The default language.
	 *
	 * @var string
	 */
	protected $defaultLanguage;

	/**
	 * Contains the usable languages
	 *
	 * @var array
	 */
	protected $usableLanguages = array();

	/**
	 * The current language code.
	 *
	 * @var string
	 */
	protected $currentLanguage;

	/**
	 * Constructor
	 *
	 * @param \YapepBase\Request\IRequest $request           The request instance
	 * @param array                       $routes            The list of available routes
	 * @param string                      $defaultLanguage   The default language code to use.
	 * @param array                       $usableLanguages   Contains the usable languages.
	 * @param IReverseRouter              $reverseRouter     The reverse router to use.
	 */
	public function __construct(
		IRequest $request, array $routes, $defaultLanguage, $usableLanguages, IReverseRouter $reverseRouter = null
	) {
		$this->defaultLanguage = $defaultLanguage;
		$this->usableLanguages = $usableLanguages;

		$this->currentLanguage = $this->getCurrentLanguageFromRequest($request, $usableLanguages, $defaultLanguage);

		parent::__construct($request, $routes,
			empty($reverseRouter)
				? new LanguageArrayReverseRouter($routes, $this->currentLanguage, $this->defaultLanguage)
				: $reverseRouter
		);
	}

	/**
	 * Returns the current language from the request object.
	 *
	 * @param IRequest $request
	 *
	 * @return string
	 */
	public function getCurrentLanguageFromRequest(IRequest $request, array $usableLanguages, $defaultLanguage) {
		$uri      = $request->getTarget();
		$uriParts = explode('/', trim($uri, '/'));

		return in_array($uriParts[0], $usableLanguages) ? $uriParts[0] : $defaultLanguage;
	}

	/**
	 * Returns the target of the request.
	 *
	 * @return string
	 */
	protected function getTarget() {
		$uri = $this->request->getTarget();

		$languageFound = false;
		// The current language is the first part of the URI
		if (strpos($uri, '/' . $this->currentLanguage) === 0
			&&
			(
				strlen($uri) == 3
				||
				$uri[3] == '/'
			)
		) {
			$languageFound = true;
		}

		// If the language is in the URI we have remove it
		return $languageFound
			? (string)substr($uri, 3)
			: $uri;
	}

	/**
	 * Returns the current language code.
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->currentLanguage;
	}
}