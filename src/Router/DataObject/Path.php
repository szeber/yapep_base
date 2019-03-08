<?php
declare(strict_types=1);

namespace YapepBase\Router\DataObject;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\DataObject\Param\IParam;

class Path
{

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

    /**
     * @param array $path
     *
     * @return Path
     */
    public static function createFromArray(array $path): self
    {
        if (!isset($path['pathPattern'])) {
            throw new InvalidArgumentException('No path pattern set for path');
        }

        if (!is_string($path['pathPattern'])) {
            throw new InvalidArgumentException(
                'Invalid path pattern. Expected string, got ' . gettype($path['pathPattern'])
            );
        }

        $pattern = '/' . trim($path['pathPattern'], "/ \r\n\t\0");
        $params = [];

        foreach ($path['params'] ?? [] as $index => $paramData) {
            if (empty($paramData['type'])) {
                throw new InvalidArgumentException('No type set for path ' . $pattern . ' param ' . $index);
            }

            $type = $paramData['type'];

            if (isset(IParam::BUILT_IN_TYPE_MAP[$type])) {
                $paramClass = IParam::BUILT_IN_TYPE_MAP[$type];
            } else {
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

            /** @var IParam $paramClass */
            $params[] = $paramClass::createFromArray($paramData);
        }

        return new static($pattern, $params);
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
