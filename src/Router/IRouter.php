<?php
declare(strict_types = 1);

namespace YapepBase\Router;

/**
 * Router interface
 */
interface IRouter extends IReverseRouter
{
    /**
     * Returns a controller and an action for the request's target.
     *
     * @param string $controllerClassName The controller class name. (Outgoing parameter)
     * @param string $actionName          The action name in the controller class. (Outgoing parameter)
     * @param string $uri                 The uri to check. If not given, the current uri will be used.
     *
     * @return string   The controller and action separated by a '/' character.
     *
     * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
     */
    public function getRoute(string &$controllerClassName, string &$actionName, ?string $uri = null): string;
}
