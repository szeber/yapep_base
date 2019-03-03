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
    protected $params;

    public function __construct(array $path)
    {
        if (!isset($path['pathPattern'])) {
            throw new InvalidArgumentException('No path pattern set for path');
        }

        if (!is_string($path['pathPattern'])) {
            throw new InvalidArgumentException(
                'Invalid path pattern. Expected string, got ' . gettype($path['pathPattern'])
            );
        }

        $this->pattern = '/' . trim($path['pathPattern'], "/ \r\n\t\0");
        $this->params = [];

        // TODO the parsing the params is not required for reverse routing, but the path itself is. Figure out if it makes sense to only parse the params if needed

        foreach ($path['params'] ?? [] as $index => $paramData) {
            if (empty($paramData['type'])) {
                throw new InvalidArgumentException('No type set for path ' . $this->pattern . ' param ' . $index);
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

            /** @var IParam $param */
            $param = new $paramClass($paramData);

            $this->params[] = $param;
        }
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
