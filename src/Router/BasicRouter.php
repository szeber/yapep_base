<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Request\IRequest;
use YapepBase\Router\Entity\ControllerAction;
use YapepBase\Router\Entity\Route;
use YapepBase\Router\Exception\RouteNotFoundException;

class BasicRouter implements IRouter
{
    /** @var Route[] */
    protected $routesByControllerAction = [];

    /** @var Route[] */
    protected $routesByName = [];

    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routesByControllerAction = [];
        $this->routesByName             = [];

        foreach ($routes as $route) {
            $this->routesByControllerAction[$route->getControllerAction()] = $route;

            if (!empty($route->getName())) {
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

    public function getPathByControllerAndAction(string $controller, string $action, array $routeParams = []): string
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

    public function getControllerActionByMethodAndPath(string $method, string $path): ControllerAction
    {
        // Normalise the path to start with a single slash and not have a trailing slash
        $path = '/' . trim($path, '/');

        foreach ($this->routesByControllerAction as $route) {
            $controllerAction = $route->matchMethodAndPath($method, $path);

            if ($controllerAction) {
                return $controllerAction;
            }
        }

        throw new RouteNotFoundException('Route not found for method ' . $method . ' and path ' . $path);
    }

    public function getControllerActionByRequest(IRequest $request): ControllerAction
    {
        return $this->getControllerActionByMethodAndPath($request->getMethod(), $request->getTarget());
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
