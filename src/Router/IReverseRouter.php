<?php
declare(strict_types = 1);

namespace YapepBase\Router;

/**
 * Reverse router interface.
 */
interface IReverseRouter
{
    /**
     * Returns the target (eg. URL) for the controller and action
     *
     * @throws \YapepBase\Exception\RouterException   On errors. (Including if the route is not found)
     */
    public function getTargetForControllerAction(string $controller, string $action, array $requestParams = []): string;
}
