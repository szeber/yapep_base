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


/**
 * Adds translated route support to the LanguageArrayReverseRouter.
 *
 * {@inheritdoc}
 *
 * @package    YapepBase
 * @subpackage Router
 */
class TranslatedLanguageArrayReverseRouter extends LanguageArrayReverseRouter implements ILanguageReverseRouter {

	/**
	 * Returns the array of routes.
	 *
	 * @return array
	 */
	protected function getRouteArray() {
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
		$originalLanguage = $this->currentLanguage;
		try {
			$this->currentLanguage = $language;
			$result = $this->getTargetForControllerAction($controller, $action, $params);
			$this->currentLanguage = $originalLanguage;
			return $result;
		}
		catch (\Exception $e) {
			$this->currentLanguage = $originalLanguage;
			throw $e;
		}
	}

}
