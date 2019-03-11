<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Request\IRequest;
use YapepBase\Router\DataObject\ControllerAction;
use YapepBase\Router\Exception\FunctionNotSupportedException;

/**
 * Generates the controller and action name based on the received target.
 */
class AutoRouter implements IRouter
{
    public function getControllerActionByRequest(IRequest $request): ControllerAction
    {
        return $this->getControllerActionByMethodAndPath($request->getMethod(), $request->getTarget());
    }

    public function getControllerActionByMethodAndPath(string $method, string $path): ControllerAction
    {
        $pathParts           = explode('/', trim($path, '/ '));
        $controllerClassName = $this->getControllerClassName($pathParts);
        $actionName          = $this->getActionName($pathParts);

        return new ControllerAction($controllerClassName, $actionName, $pathParts);
    }

    protected function getControllerClassName(array &$uriParts): string
    {
        $controllerClassName = array_shift($uriParts);

        if (empty($controllerClassName)) {
            $controllerClassName = 'Index';
        } else {
            $controllerClassName = $this->convertPathPartToName($controllerClassName);
        }

        return $controllerClassName;
    }

    protected function getActionName(array &$uriParts): string
    {
        if (empty($uriParts)) {
            $actionName = 'Index';
        } else {
            $actionName = $this->convertPathPartToName(array_shift($uriParts));
        }

        return $actionName;
    }

    /**
     * Converts a path part to a controller or action name.
     */
    protected function convertPathPartToName(string $pathPart): string
    {
        $parts = preg_split('/[-_ A-Z]/', preg_replace('/[^-_a-zA-Z0-9]/', '', $pathPart));
        foreach ($parts as $key => $value) {
            $parts[$key] = ucfirst($value);
        }
        return implode('', $parts);
    }

    /**
     * Converts a controller or action name to a path part
     */
    protected function convertNameToPathPart(string $controllerOrActionName): string
    {
        return strtolower(substr($controllerOrActionName, 0, 1)) . substr($controllerOrActionName, 1);
    }

    /**
     * @inheritdoc
     */
    public function getPathByControllerAndAction(string $controller, string $action, array $routeParams = []): string
    {
        if ('Index' == $action && 'Index' == $controller && empty($routeParams)) {
            $path = '/';
        } elseif ('Index' == $action && empty($routeParams)) {
            $path = '/' . $this->convertNameToPathPart($controller);
        } else {
            $path = '/' . $this->convertNameToPathPart($controller) . '/' . $this->convertNameToPathPart($action);
        }
        if (!empty($routeParams)) {
            $path .= '/' . implode('/', $routeParams);
        }
        return $path;
    }

    public function getPathByName(string $name, array $routeParams = []): string
    {
        throw new FunctionNotSupportedException('The auto router does not support name based routes');
    }
}
