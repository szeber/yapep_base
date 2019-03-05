<?php
declare(strict_types=1);

namespace YapepBase\Router\DataObject;

use YapepBase\Exception\IException;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\IAnnotation;

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
        $this->controller          = $route['controller'] ?? '';
        $this->action              = $route['action'] ?? '';
        $this->methods             = $route['methods'] ?? [];
        $this->regexPatterns       = $route['regexPatterns'] ?? null;
        $this->unparsedPaths       = $route['paths'] ?? [];
        $this->unparsedAnnotations = $route['annotations'] ?? [];
    }

    private function validate(array $route): void
    {
        if (empty($route)) {
            throw new InvalidArgumentException('The route array is empty');
        }

        if (empty($route['controller'])) {
            throw new InvalidArgumentException('No controller is specified for route');
        }

        if (empty($route['action'])) {
            throw new InvalidArgumentException('No action is specified for route');
        }

        if (isset($route['methods']) && !is_array($route['methods'])) {
            throw new InvalidArgumentException('The methods should be an array in the route');
        }

        if (isset($route['regexPatterns']) && !is_array($route['regexPatterns'])) {
            throw new InvalidArgumentException('The regexPatters should be an array in the route');
        }

        if (!isset($route['paths']) || !is_array($route['paths'])) {
            throw new InvalidArgumentException('No paths specified or the paths is not an array for route');
        }

        if (isset($route['annotations']) && !is_array($route['annotations'])) {
            throw new InvalidArgumentException('No annotations should be an array in the route');
        }

        $this->parsePaths($route['paths']);
        $this->parseAnnotations($route['annotations'] ?? []);
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
        if (null !== $this->paths) {
            return $this->paths;
        }

        try {
            $this->paths = $this->parsePaths($this->unparsedPaths);
        } catch (IException $e) {
            $exceptionClass = get_class($e);

            throw new $exceptionClass(
                'Exception while processing paths for route ' . $this->getControllerAction() . '. Error: '
                    . $e->getMessage(),
                $e->getCode(),
                $e
            );
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
        if (null !== $this->annotations) {
            return $this->annotations;
        }

        try {
            $this->annotations = $this->parseAnnotations($this->unparsedAnnotations);
        } catch (IException $e) {
            $exceptionClass = get_class($e);

            throw new $exceptionClass(
                'Exception while processing annotations for route ' . $this->getControllerAction() . '. Error: '
                . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $this->annotations;
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

                return new ControllerAction($this->controller, $this->action, $matches, $this->getAnnotations());
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

    /**
     * @param array $paths
     *
     * @return Path[]
     */
    public function parsePaths(array $paths): array
    {
        $parsedPaths = [];

        foreach ($paths as $path) {
            $parsedPaths[] = new Path($path);
        }

        return $parsedPaths;
    }

    /**
     * @param array $annotations
     *
     * @return IAnnotation[]
     */
    public function parseAnnotations(array $annotations): array
    {
        $parsedAnnotations = [];

        foreach ($annotations as $annotationClass => $annotation) {
            if (!class_exists($annotationClass, true)) {
                throw new InvalidArgumentException('Class ' . $annotationClass . ' not found for annotation');
            }

            if (!in_array(IAnnotation::class, class_implements($annotationClass, false))) {
                throw new InvalidArgumentException(
                    'Invalid annotation class: ' . $annotationClass . '. It should implement ' . IAnnotation::class
                );
            }

            $parsedAnnotations[] = new $annotationClass($annotation);
        }

        return $parsedAnnotations;
    }
}
