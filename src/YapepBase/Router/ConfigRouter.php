<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Exception\RouterException;
use YapepBase\Config;
use YapepBase\Request\IRequest;

/**
 * Routes a request based on an array stored in a config variable.
 * The config variable's structure should match the config for an ArrayRouter {@see \YapepBase\Router\ArrayRouter}.
 *
 * Configuration variable's name should be set in the format:
 * <b>resource.routing.&lt;configName&gt;
 */
class ConfigRouter extends ArrayRouter
{

    /**
     * @param IRequest       $request       The request instance
     * @param string         $configName    The name of the configuration where the routes are stored.
     * @param IReverseRouter $reverseRouter The reverse router to use. If not set, it will use an ArrayReverseRouter.
     *
     * @throws RouterException   On error
     */
    public function __construct(IRequest $request, string $configName, ?IReverseRouter $reverseRouter = null)
    {
        $routes = Config::getInstance()->get('resource.routing.' . $configName, false);

        if (!is_array($routes)) {
            throw new RouterException(
                'No route config found for name: ' . $configName,
                RouterException::ERR_ROUTE_CONFIG
            );
        }

        parent::__construct($request, $routes, $reverseRouter);
    }

}
