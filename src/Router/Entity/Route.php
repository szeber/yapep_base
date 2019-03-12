<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\IAnnotation;

class Route
{
    public const KEY_PATHS          = 'paths';
    public const KEY_REGEX_PATTERNS = 'regexPatterns';
    public const KEY_METHODS        = 'methods';
    public const KEY_ACTION         = 'action';
    public const KEY_CONTROLLER     = 'controller';
    public const KEY_NAME           = 'name';
    public const KEY_ANNOTATIONS    = 'annotations';

    /** @var string|null */
    protected $name;

    /** @var string */
    protected $controller;

    /** @var string */
    protected $action;

    /** @var string[] */
    protected $methods = [];

    /** @var string[] */
    protected $regexPatterns = [];

    /** @var Path[] */
    protected $paths = [];

    /** @var IAnnotation[] */
    protected $annotations = [];

    public function __construct(
        string $controller,
        string $action,
        ?string $name,
        array $methods,
        array $regexPatterns,
        array $paths,
        array $annotations
    ) {
        $this->name          = $name;
        $this->controller    = $controller;
        $this->action        = $action;
        $this->methods       = $methods;
        $this->regexPatterns = $regexPatterns;
        $this->paths         = $paths;
        $this->annotations   = $annotations;
    }

    /**
     * @param array $state
     *
     * @return static
     */
    public static function __set_state(array $state): self
    {
        return new static(
            $state[self::KEY_CONTROLLER],
            $state[self::KEY_ACTION],
            $state[self::KEY_NAME],
            $state[self::KEY_METHODS],
            $state[self::KEY_REGEX_PATTERNS],
            $state[self::KEY_PATHS],
            $state[self::KEY_ANNOTATIONS]
        );
    }

    /**
     * @param array $route
     *
     * @return static
     */
    public static function createFromArray(array $route): self
    {
        static::validate($route);

        $paths       = static::parsePaths($route[self::KEY_PATHS]);
        $annotations = static::parseAnnotations($route[self::KEY_ANNOTATIONS] ?? []);

        $regexPatterns = array_map(
            function (Path $path) {
                return $path->getRegexPattern();
            },
            $paths
        );

        return new static(
            $route[self::KEY_CONTROLLER],
            $route[self::KEY_ACTION],
            $route[self::KEY_NAME] ?? null,
            $route[self::KEY_METHODS] ?? [],
            $regexPatterns,
            $paths,
            $annotations
        );
    }

    public function toArray(): array
    {
        $pathsArray = [];
        foreach ($this->paths as $annotation) {
            $pathsArray[] = $annotation->toArray();
        }
        $annotationsArray = [];
        foreach ($this->annotations as $annotation) {
            $annotationsArray[get_class($annotation)] = $annotation->toArray();
        }

        return [
            self::KEY_CONTROLLER     => $this->controller,
            self::KEY_ACTION         => $this->action,
            self::KEY_NAME           => $this->name,
            self::KEY_METHODS        => $this->methods,
            self::KEY_REGEX_PATTERNS => $this->regexPatterns,
            self::KEY_PATHS          => $pathsArray,
            self::KEY_ANNOTATIONS    => $annotationsArray
        ];
    }

    private static function validate(array $route): void
    {
        if (empty($route)) {
            throw new InvalidArgumentException('The route array is empty');
        }

        if (empty($route[self::KEY_CONTROLLER])) {
            throw new InvalidArgumentException('No controller is specified for route');
        }

        if (empty($route[self::KEY_ACTION])) {
            throw new InvalidArgumentException('No action is specified for route');
        }

        if (isset($route[self::KEY_METHODS]) && !is_array($route[self::KEY_METHODS])) {
            throw new InvalidArgumentException('The methods should be an array in the route');
        }

        if (isset($route[self::KEY_REGEX_PATTERNS]) && !is_array($route[self::KEY_REGEX_PATTERNS])) {
            throw new InvalidArgumentException('The regexPatterns should be an array in the route');
        }

        if (!isset($route[self::KEY_PATHS]) || !is_array($route[self::KEY_PATHS])) {
            throw new InvalidArgumentException('No paths specified or the paths is not an array for route');
        }

        if (isset($route[self::KEY_ANNOTATIONS]) && !is_array($route[self::KEY_ANNOTATIONS])) {
            throw new InvalidArgumentException('Annotations should be an array in the route');
        }
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
        return $this->paths;
    }

    /**
     * @return string[]
     */
    public function getRegexPatterns(): array
    {
        return $this->regexPatterns;
    }

    /**
     * @return IAnnotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
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
    private static function parsePaths(array $paths): array
    {
        $parsedPaths = [];

        foreach ($paths as $path) {
            $parsedPaths[] = Path::createFromArray($path);
        }

        return $parsedPaths;
    }

    /**
     * @param array $annotations
     *
     * @return IAnnotation[]
     */
    private static function parseAnnotations(array $annotations): array
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

            /** @var IAnnotation $annotationClass */
            $parsedAnnotations[] = $annotationClass::createFromArray($annotation);
        }

        return $parsedAnnotations;
    }
}
