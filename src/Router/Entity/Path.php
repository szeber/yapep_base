<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Param\IParam;
use YapepBase\Router\Entity\Param\Mapper;

class Path
{
    const ARRAY_KEY_PATTERN    = 'pathPattern';
    const ARRAY_KEY_PARAMS     = 'params';
    const ARRAY_KEY_PARAM_TYPE = 'type';

    /** @var string */
    protected $pattern;

    /** @var IParam[] */
    protected $params = [];

    /**
     * @param string   $pattern
     * @param IParam[] $params
     */
    public function __construct(string $pattern, array $params)
    {
        $this->pattern = $pattern;
        $this->params  = $params;
    }

    /**
     * @param $state array
     *
     * @return static
     */
    public static function __set_state($state): self
    {
        return new static(
            $state['pattern'],
            $state['params']
        );
    }

    public static function createFromArray(array $path): self
    {
        $pattern = static::getPatternFromArray($path);
        $params  = [];

        foreach ($path[self::ARRAY_KEY_PARAMS] ?? [] as $index => $paramData) {
            if (empty($paramData[self::ARRAY_KEY_PARAM_TYPE])) {
                throw new InvalidArgumentException('No type set for path ' . $pattern . ' param ' . $index);
            }

            /** @var IParam $paramClass */
            $paramClass = static::getParamClass($paramData[self::ARRAY_KEY_PARAM_TYPE]);

            $params[] = $paramClass::createFromArray($paramData);
        }

        return new static($pattern, $params);
    }

    public function toArray(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            $paramData                             = $param->toArray();
            $paramData[self::ARRAY_KEY_PARAM_TYPE] = $this->getParamType($param);

            $params[] = $paramData;
        }

        return [
            self::ARRAY_KEY_PATTERN => $this->pattern,
            self::ARRAY_KEY_PARAMS  => $params
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function getPatternFromArray(array $path): string
    {
        if (!isset($path[self::ARRAY_KEY_PATTERN])) {
             throw new InvalidArgumentException('No path pattern set for path');
         }

         if (!is_string($path[self::ARRAY_KEY_PATTERN])) {
             throw new InvalidArgumentException(
                 'Invalid path pattern. Expected string, got ' . gettype($path['pathPattern'])
             );
         }

        return '/' . trim($path[self::ARRAY_KEY_PATTERN], "/ \r\n\t\0");
    }

    protected static function getParamClass(string $type): string
    {
        try {
            $paramClass = Mapper::getClassByType($type);
        }
        catch (InvalidArgumentException $e) {
            $paramClass = $type;
        }

        if (!class_exists($paramClass, true)) {
            throw new InvalidArgumentException('Class ' . $paramClass . ' not found for path parameter');
        }

        if (!in_array(IParam::class, class_implements($paramClass, false))) {
            throw new InvalidArgumentException(
                'Invalid path param class: ' . $paramClass . '. It should implement ' . IParam::class
            );
        }

        return $paramClass;
    }

    protected function getParamType(IParam $param)
    {
        $class = get_class($param);
        return Mapper::getTypeByClass($class);
    }

    public function getRegexPattern(): string
    {
        $pattern = '#^' . $this->pattern . '$#';

        foreach ($this->params as $param) {
            $paramName = preg_quote($param->getName(), '#');
            $regexPart = '(?P<' . $paramName . '>' . addcslashes($param->getPattern(), '#') . ')';
            $pattern   = str_replace('{' . $param->getName() . '}', $regexPart, $pattern);
        }

        return $pattern;
    }

    protected function getParamNames(): array
    {
        preg_match_all('#\{([-_a-zA-Z0-9]+)\}#', $this->pattern, $matches, PREG_PATTERN_ORDER);

        return $matches[1] ?? [];
    }

    public function getParameterisedPath(array $routeParams): ?string
    {
        $paramNames = $this->getParamNames();

        if (count($routeParams) !== count($paramNames)) {
            return null;
        }

        if (!empty(array_diff($paramNames, array_keys($routeParams)))) {
            return null;
        }

        $path = $this->pattern;

        foreach ($routeParams as $name => $value) {
            $path = str_replace('{' .  $name . '}', $value, $path);
        }

        return $path;
    }
}
