<?php
declare(strict_types=1);

namespace YapepBase\Router\DataObject;

use YapepBase\Exception\IException;

class Route
{

    /** @var string|null */
    protected $name;

    /** @var string */
    protected $controller;

    /** @var string */
    protected $action;

    /** @var string[] */
    protected $methods = [];

    /** @var string[]|null */
    protected $regexPatterns;

    /** @var array */
    protected $unparsedPaths = [];

    /** @var array */
    protected $unparsedAnnotations = [];

    /** @var Path[]|null */
    protected $paths;

    /** @var IAnnotation[]|null */
    protected $annotations;

    public function __construct(array $route, bool $validate = true)
    {
        if ($validate) {
            $this->validate($route);
        }

        $this->name                = $route['name'] ?? null;
        $this->controller          = $route['controller'];
        $this->action              = $route['action'];
        $this->methods             = $route['methods'] ?? [];
        $this->regexPatterns       = $route['regexPatterns'] ?? null;
        $this->unparsedPaths       = $route['paths'];
        $this->unparsedAnnotations = $route['annotations'];
    }

    private function validate($route)
    {
        // TODO implement validation
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getControllerAction(): string
    {
        return $this->controller . '/' . $this->action;
    }

    /**
     * @return Path[]
     */
    public function getPaths(): array
    {
        if (null === $this->paths) {
            try {
                $this->paths = [];

                foreach ($this->unparsedPaths as $unparsedPath) {
                    $this->paths[] = new Path($unparsedPath);
                }

            } catch (IException $e) {
                $this->paths = null;

                $exceptionClass = get_class($e);

                throw new $exceptionClass(
                    'Exception while processing paths for route ' . $this->getControllerAction() . '. Error: '
                        . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $this->paths;
    }

    /**
     * @return string[]
     */
    public function getRegexPatterns(): array
    {
        if (null === $this->regexPatterns) {
            $this->regexPatterns = [];

            foreach ($this->getPaths() as $path) {
                $this->regexPatterns[] = $path->getRegexPattern();
            }
        }

        return $this->regexPatterns;
    }

    /**
     * @return IAnnotation[]
     */
    public function getAnnotations(): array
    {
        // TODO implement
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'controller'    => $this->controller,
            'action'        => $this->action,
            'methods'       => $this->methods,
            'regexPatterns' => $this->getRegexPatterns(),
            'paths'         => $this->unparsedPaths,
            'annotations'   => $this->unparsedAnnotations,
        ];
    }

    public function matchMethodAndPath(string $method, string $path): ?ControllerAction
    {
        // If the method doesn't match we don't need to check the path
        if (!empty($this->methods) && !in_array($method, $this->methods, true)) {
            return null;
        }

        foreach ($this->getRegexPatterns() as $regexPattern) {
            if (preg_match($regexPattern, $path, $matches)) {
                // Match found, remove the full path from the matches, set the rest as params
                unset($matches[0]);

                return new ControllerAction($this->controller, $this->action, $matches);
            }
        }

        return null;
    }

    public function getParameterisedPath(array $routeParams = []): ?string
    {
        foreach ($this->getPaths() as $path) {
            $parameterisedPath = $path->getParameterisedPath($routeParams);

            if (null !== $parameterisedPath) {
                return $parameterisedPath;
            }
        }

        return null;
    }
}
