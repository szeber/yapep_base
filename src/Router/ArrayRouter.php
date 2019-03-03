<?php
declare(strict_types=1);

namespace YapepBase\Router;


use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;

/**
 * Routes a request based on the specified associative array.
 */
class ArrayRouter implements IRouter
{
    /** @var IRequest */
    protected $request;

    /** @var array */
    protected $routes;

    /** @var IReverseRouter|null */
    protected $reverseRouter;

    public function __construct(IRequest $request, array $routes, ?IReverseRouter $reverseRouter = null)
    {
        if (is_null($reverseRouter)) {
            $reverseRouter = new ArrayReverseRouter($routes);
        }

        $this->request       = $request;
        $this->routes        = $routes;
        $this->reverseRouter = $reverseRouter;
    }

    /**
     * Returns the target of the request.
     */
    protected function getTarget(?string $uri): string
    {
        $target = empty($uri) ? rtrim($this->request->getTarget(), '/') : $uri;

        // If the target doesn't start with a '/', add one
        if ('/' != substr($target, 0, 1)) {
            $target = '/' . $target;
        }

        return $target;
    }

    /**
     * Returns the method of the request.
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * @inheritdoc
     */
    public function getRoute(string &$controllerClassName = '', string &$actionName = '', ?string $uri = null): string
    {
        $target = $this->getTarget($uri);
        $method = $this->getMethod();

        foreach ($this->routes as $controllerAction => $path) {
            if ($this->testIfPathMatchesTarget($path, $method, $target)) {
                list($controllerClassName, $actionName) = explode('/', $controllerAction, 2);
                return $controllerAction;
            }
        }

        // There was no valid route
        throw new RouterException('No route found for path: ' . $target, RouterException::ERR_NO_ROUTE_FOUND);
    }

    protected function testIfPathMatchesTarget(string $path, string $method, string $target): bool
    {
        if (is_array($path)) {
            foreach ($path as $value) {
                if ($this->testIfPathMatchesTarget($value, $method, $target)) {
                    return true;
                }
            }
            return false;
        } elseif ('[' == substr($path, 0, 1)) {
            // This path is restricted to a method
            list($pathMethod, $path) = explode(']', substr($path, 1), 2);
            if ($method != $pathMethod) {
                // The method does not match, continue with the next path
                return false;
            }
        }

        $path = trim($path);

        if ('/' != substr($path, 0, 1)) {
            // If the path doesn't start with a '/', add one
            $path = '/' . $path;
        }

        if ($this->checkPathHasParams($path)) {
            return $this->setRequestParams($path, $target);
        } else {
            if ($path == $target) {
                return true;
            }
        }

        return false;
    }

    protected function checkPathHasParams(string $path): bool
    {
        return strpos($path, '{') !== false;
    }

    protected function setRequestParams(string $path, string $target): bool
    {
        $firstParamPosition = strpos($path, '{');

        if (substr($path, 0, $firstParamPosition) != substr($target, 0, $firstParamPosition)) {
            // The static part of the path doesn't match, continue with the next path
            return false;
        }
        $regex  = $this->getRegexForPath($path);
        $params = [];
        if (!preg_match($regex, $target, $params)) {
            // The target doesn't match the path
            return false;
        }

        foreach ($params as $name => $value) {
            if (is_numeric($name)) {
                continue;
            }
            $this->request->setParam($name, $value);
        }
        return true;
    }

    /**
     * Creates a regex from the parameterized route path.
     *
     * @throws RouterException
     */
    protected function getRegexForPath($path): string
    {
        $matches = [];
        if (!preg_match_all('/\{([-_.a-zA-Z0-9]+):([-_.a-zA-Z0-9]+)(\(([^}]*)\))?\}/', $path, $matches, PREG_SET_ORDER)) {
            throw new RouterException('Invalid param syntax in route: ' . $path, RouterException::ERR_SYNTAX_PARAM);
        }

        $pathRegex = '/^' . preg_quote($path, '/') . '$/';

        foreach ($matches as $match) {
            switch ($match[2]) {
                case 'alpha':
                    $pattern = '[[:alpha:]]+';
                    break;

                case 'num':
                    $pattern = '\d+';
                    break;

                case 'alnum':
                    $pattern = '[[:alnum:]]+';
                    break;

                case 'regex':
                    if (empty($match[3])) {
                        throw new RouterException('Regex param type without pattern: ' . $path, RouterException::ERR_SYNTAX_PARAM);
                    }
                    $pattern = $match[3];
                    break;

                case 'enum':
                    if (empty($match[3])) {
                        throw new RouterException('Enum param type without values: ' . $path, RouterException::ERR_SYNTAX_PARAM);
                    }
                    $pattern = $match[3];
                    break;

                default:
                    throw new RouterException('Invalid param type in route: ' . $match[1], RouterException::ERR_SYNTAX_PARAM);
                    break;
            }

            $count     = 0;
            $pathRegex = str_replace(preg_quote($match[0], '/'), '(?P<' . preg_quote($match[1], '/') . '>' . $pattern . ')', $pathRegex, $count);

            if (1 != $count) {
                throw new RouterException('Duplicate route param name: ' . $path, RouterException::ERR_SYNTAX_PARAM);
            }
        }
        return $pathRegex;
    }

    /**
     * @inheritdoc
     */
    public function getTargetForControllerAction(string $controller, string $action, array $requestParams = []): string
    {
        return $this->reverseRouter->getTargetForControllerAction($controller, $action, $requestParams);
    }
}
