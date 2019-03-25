<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Router\Exception\RouteNotFoundException;
use YapepBase\Router\Exception\RouterException;

interface IReverseRouter
{
    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getPathByControllerAndAction(string $controller, string $action, array $routeParams = []): string;

    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getPathByName(string $name, array $routeParams = []): string;
}
