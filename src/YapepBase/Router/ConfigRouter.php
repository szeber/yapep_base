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
use YapepBase\Config;
use YapepBase\Request\IRequest;

/**
 * ConfigRouter class.
 *
 * Routes a request based on an array stored in a config variable.
 * The config variable's structure should match the config for an ArrayRouter {@see \YapepBase\Router\ArrayRouter}.
 *
 * Configuration variable's name should be set in the format:
 * <b>resource.routing.&lt;configName&gt;
 *
 * @package    YapepBase
 * @subpackage Router
 */
class ConfigRouter extends ArrayRouter {

	/**
	 * Constructor.
	 *
	 * @param \YapepBase\Request\IRequest      $request         The request instance
	 * @param string                           $configName      The name of the configuration where the routes are
	 *                                                          stored.
	 * @param \YapepBase\Router\IReverseRouter $reverseRouter   The reverse router to use. If not set, it will use
	 *                                                          an ArrayReverseRouter.
	 *
	 * @throws RouterException   On error
	 */
	public function __construct(IRequest $request, $configName, IReverseRouter $reverseRouter = null) {
		$routes = Config::getInstance()->get('resource.routing.' . $configName, false);
		if (!is_array($routes)) {
			throw new RouterException('No route config found for name: ' . $configName,
				RouterException::ERR_ROUTE_CONFIG);
		}
		parent::__construct($request, $routes, $reverseRouter);
	}

}