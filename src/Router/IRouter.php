<?php
declare(strict_types=1);

namespace YapepBase\Router;


use YapepBase\Request\IRequest;
use YapepBase\Router\DataObject\ControllerAction;
use YapepBase\Router\Exception\RouteNotFoundException;
use YapepBase\Router\Exception\RouterException;

/**
 * Router interface
 */
interface IRouter extends IReverseRouter
{

    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getRouteByRequest(IRequest $request): ControllerAction;

    /**
     * @throws RouteNotFoundException
     * @throws RouterException
     */
    public function getRoute(string $method, string $path): ControllerAction;
}
