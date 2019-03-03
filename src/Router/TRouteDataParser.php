<?php
declare(strict_types = 1);

namespace YapepBase\Router;

use YapepBase\Router\DataObject\Route;

trait TRouteDataParser
{
    /** @var Route[] */
    protected $routesByControllerAction;

    protected function populateRoutes(array $routeData)
    {
        $this->routesByControllerAction = [];

        foreach ($routeData as $route) {
            $routeObject                                                         = new Route($route);
            $this->routesByControllerAction[$routeObject->getControllerAction()] = $routeObject;
        }

    }
}
