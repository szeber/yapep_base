<?php
declare(strict_types = 1);

namespace YapepBase\Router\Caching;

use Symfony\Component\Yaml\Yaml;
use YapepBase\Router\Entity\Route;

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
            $this->routes[] = Route::createFromArray($parsedRoute);
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
