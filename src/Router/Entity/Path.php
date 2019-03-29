<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Param\IParam;
use YapepBase\Router\Entity\Param\ParamAbstract;

class Path
{
    const ARRAY_KEY_PATTERN = 'pathPattern';
    const ARRAY_KEY_PARAMS  = 'params';

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
        $this->validateParams($params);

        $this->pattern = $pattern;
        $this->params  = $params;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return IParam[]
     */
    public function getParams(): array
    {
        return $this->params;
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
            if (empty($paramData[ParamAbstract::ARRAY_KEY_CLASS])) {
                throw new InvalidArgumentException('No class set for path ' . $pattern . ' param ' . $index);
            }

            /** @var IParam $paramClass */
            $paramClass = $paramData[ParamAbstract::ARRAY_KEY_CLASS];

            if (!is_subclass_of($paramClass, IParam::class)) {
                throw new InvalidArgumentException('Only Params can be set');
            }

            $params[] = $paramClass::createFromArray($paramData);
        }

        return new static($pattern, $params);
    }

    public function toArray(): array
    {
        $params = [];
        foreach ($this->params as $param) {
            $paramData = $param->toArray();

            $params[] = $paramData;
        }

        return [
            self::ARRAY_KEY_PATTERN => $this->pattern,
            self::ARRAY_KEY_PARAMS  => $params,
        ];
    }

    public function getParameterisedPath(array $routeParamsByName): ?string
    {
        $paramNames = $this->getParamNames();

        if (count($routeParamsByName) !== count($paramNames)) {
            return null;
        }

        if (!empty(array_diff($paramNames, array_keys($routeParamsByName)))) {
            return null;
        }

        $path = $this->pattern;

        foreach ($routeParamsByName as $name => $value) {
            $path = str_replace('{' . $name . '}', $value, $path);
        }

        return $path;
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

    /**
     * @throws InvalidArgumentException
     */
    protected function validateParams(array $params): void
    {
        foreach ($params as $value) {
            if (!($value instanceof IParam)) {
                throw new InvalidArgumentException('Params should implement IParam');
            }
        }
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
            throw new InvalidArgumentException('Invalid path pattern. Expected string, got ' . gettype($path['pathPattern']));
        }

        return '/' . trim($path[self::ARRAY_KEY_PATTERN], "/ \r\n\t\0");
    }

    protected function getParamNames(): array
    {
        preg_match_all('#\{([-_a-zA-Z0-9]+)\}#', $this->pattern, $matches, PREG_PATTERN_ORDER);

        return $matches[1] ?? [];
    }
}
