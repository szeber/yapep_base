<?php
declare(strict_types = 1);

namespace YapepBase\Router;

use YapepBase\Router\DataObject\Route;

trait TRouteDataParser
{
    /** @var Route[] */
    protected $routesByControllerAction;

    /**
     * @param Route[] $routeData
     *
     * @return void
     */
    protected function populateRoutes(array $routeData)
    {
        $this->routesByControllerAction = [];

        foreach ($routeData as $route) {
            $this->routesByControllerAction[$route->getControllerAction()] = $route;
        }

    }
}
