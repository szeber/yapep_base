<?php
declare(strict_types = 1);

namespace YapepBase\Router\Caching;

use Symfony\Component\Yaml\Yaml;
use YapepBase\Router\DataObject\Route;

class YamlCollector implements IRouteCollector
{
    /** @var Route[] */
    protected $routes = [];

    public function __construct(string $path)
    {
        // TODO error handling
        $this->path = $path;

        $parsed = Yaml::parse(file_get_contents($path));

        foreach ($parsed as $parsedRoute) {
            $this->routes[] = new Route($parsedRoute, true);
        }
    }

    /**
     * @return Route[]
     */
    public function getCollectedRoutes(): array
    {
        return $this->routes;
    }
}
