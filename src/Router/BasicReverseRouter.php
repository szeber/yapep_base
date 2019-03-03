<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Router\DataObject\Route;
use YapepBase\Router\Exception\RouteNotFoundException;

class BasicReverseRouter implements IReverseRouter, IRouteGetter
{
    use TRouteDataParser;

    /** @var Route[] */
    protected $routesByName;

    public function __construct(array $routeData)
    {
        $this->populateRoutes($routeData);

        foreach ($this->routesByControllerAction as $route) {
            if ($route->getName()) {
                $this->routesByName[$route->getName()] = $route;
            }
        }
    }

    /**
     * @return Route[]
     */
    public function getRoutesByControllerAction(): array
    {
        return $this->routesByControllerAction;
    }

    public function getPathByControllerAction(string $controller, string $action, array $routeParams = []): string
    {
        $controllerAction = $controller . '/' . $action;

        if (!isset($this->routesByControllerAction[$controllerAction])) {
            throw new RouteNotFoundException('No route found for controller ' . $controller . ' and action ' . $action);
        }

        return $this->getParameterisedPathFromRoute($this->routesByControllerAction[$controllerAction], $routeParams);
    }

    public function getPathByName(string $name, array $routeParams = []): string
    {
        if (!isset($this->routesByName[$name])) {
            throw new RouteNotFoundException('No route found for name ' . $name);
        }

        return $this->getParameterisedPathFromRoute($this->routesByName[$name], $routeParams);
    }

    /**
     * @throws RouteNotFoundException
     */
    protected function getParameterisedPathFromRoute(Route $route, array $routeParams): string
    {
        $path = $route->getParameterisedPath($routeParams);

        if (null === $path) {
            throw new RouteNotFoundException(
                'No patterns found for controller/action ' . $route->getControllerAction() . ' with parameter list: '
                    . json_encode($routeParams)
            );
        }

        return $path;
    }
}
