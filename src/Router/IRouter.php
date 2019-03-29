<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Request\IRequest;
use YapepBase\Router\Entity\ControllerAction;
use YapepBase\Router\Exception\RouteNotFoundException;
use YapepBase\Router\Exception\RouterException;

/**
 * Router interface
 */
interface IRouter
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

    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getControllerActionByRequest(IRequest $request): ControllerAction;

    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getControllerActionByMethodAndPath(string $method, string $path): ControllerAction;
}
