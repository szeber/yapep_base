<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Request\IRequest;
use YapepBase\Router\DataObject\ControllerAction;
use YapepBase\Router\Exception\RouteNotFoundException;

class BasicRouter implements IRouter
{
    use TRouteDataParser;

    /** @var IReverseRouter  */
    protected $reverseRouter;

    // TODO consider removing reverse routers all together, as the request is no longer set to the router, so it can be used as the reverse router

    public function __construct(array $routeData, ?IReverseRouter $reverseRouter = null)
    {
        if (null === $reverseRouter) {
            $reverseRouter = new BasicReverseRouter($routeData);
        }

        if ($reverseRouter instanceof IRouteGetter) {
            $this->routesByControllerAction = $reverseRouter->getRoutesByControllerAction();
        }

        $this->reverseRouter = $reverseRouter;
    }

    public function getPathByControllerAction(string $controller, string $action, array $routeParams = []): string
    {
        return $this->reverseRouter->getPathByControllerAction($controller, $action, $routeParams);
    }

    public function getPathByName(string $name, array $routeParams = []): string
    {
        return $this->reverseRouter->getPathByName($name, $routeParams);
    }

    public function getRouteByRequest(IRequest $request): ControllerAction
    {
        return $this->getRoute($request->getMethod(), $request->getTarget());
    }

    public function getRoute(string $method, string $path): ControllerAction
    {
        // Normalise the path to start with a single slash and not have a trailing slash
        $path = '/' . trim($path, '/');

        foreach ($this->routesByControllerAction as $route) {
            $controllerAction = $route->matchMethodAndPath($method, $path);

            if ($controllerAction) {
                return $controllerAction;
            }
        }

        throw new RouteNotFoundException('Route not found for method ' .$method . ' and path ' . $path);
    }
}
