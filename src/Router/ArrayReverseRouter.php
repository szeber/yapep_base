<?php
declare(strict_types = 1);

namespace YapepBase\Router;

use YapepBase\Exception\RouterException;

/**
 * Gets the route for the specified controller and action based on an associative array.
 *
 * The array's keys are in the Controller/Action format. The controller name should not include the namespace, and the
 * Action's name should not include the controller specific action prefix.
 * The array's values are the routes in the following format: [METHOD]URI
 * The method is optional (if present, the brackets are required). The URI may contain parameters in the format:
 * {paramName:paramType(options)}
 * The valid paramTypes are:
 *     <ul>
 *         <li>num: Any number. No options are allowed.</li>
 *         <li>alpha: Any alphabetic character. No options are allowed.</li>
 *         <li>alnum: Any alphanumeric character. No options are allowed.</li>
 *         <li>enum: Enumeration of the values in the options. The enumeration values should be separated by
 *                   the '|' character in the options string. Any '/' characters should be escaped.</li>
 *         <li>regex: Regular expression. The pattern should be included in the options string. The pattern should
 *                    not contain delimiters, and should be escaped for a '/' delimiter. It may not contain '{' or '}'
 *                    characters.</li>
 *     </ul>
 */
class ArrayReverseRouter implements IReverseRouter
{
    protected $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetForControllerAction(string $controller, string $action, array $requestParams = []): string
    {
        $key = $controller . '/' . $action;

        if (!isset($this->routes[$key])) {
            throw new RouterException(
                'No route found for controller and action: ' . $controller . '/' . $action,
                RouterException::ERR_NO_ROUTE_FOUND
            );
        }

        $target = null;
        if (is_array($this->routes[$key])) {
            foreach ($this->routes[$key] as $route) {
                $target = $this->getParameterizedRoute($route, $requestParams);
                if ($target !== null) {
                    break;
                }
            }
        } else {
            $target = $this->getParameterizedRoute($this->routes[$key], $requestParams);
        }

        if ($target === null) {
            throw new RouterException(
                'No exact route match for controller and action: ' . $controller . '/' . $action
                    . ' with params: ' . implode(', ', array_keys($requestParams)),
                RouterException::ERR_MISSING_PARAM
            );
        }

        if ('/' != substr($target, 0, 1)) {
            $target = '/' . $target;
        }

        return $target;
    }

    /**
     * Returns the route with the parameters
     */
    protected function getParameterizedRoute(string $route, array $params): ?string
    {
        $route = preg_replace('/^\s*\[[^\]]+\]\s*/', '', $route);
        if (strstr($route, '{')) {
            foreach ($params as $key => $value) {
                $count = 0;
                $route = preg_replace('/\{' . preg_quote($key, '/') . ':[^}]+\}/', $value, $route, -1, $count);

                if ($count < 1) {
                    return null;
                }
            }

            if (strstr($route, '{')) {
                return null;
            }
        } elseif (!empty($params)) {
            return null;
        }

        return $route;
    }
}
