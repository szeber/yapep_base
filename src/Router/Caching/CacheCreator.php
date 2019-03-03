<?php
declare(strict_types = 1);

namespace YapepBase\Router\Caching;

use YapepBase\Exception\InvalidArgumentException;

class CacheCreator
{
    /** @var IRouteCollector[] */
    protected $collectors = [];

    /**
     * @param IRouteCollector[] $collectors
     */
    public function __construct(array $collectors)
    {
        if (empty($collectors)) {
            throw new InvalidArgumentException('No collectors in array');
        }

        foreach ($collectors as $collector) {
            if (!($collector instanceof IRouteCollector)) {
                throw new InvalidArgumentException(
                    'The collectors array should only contain instances of ' . IRouteCollector::class
                );
            }
        }

        $this->collectors = $collectors;
    }

    // TODO the path should probably be built up from config, or generated by one class that reads the cache dir from config
    public function generateCache($path): void
    {
        $routes = [];

        foreach ($this->collectors as $collector) {
            foreach ($collector->getCollectedRoutes() as $route) {
                $routes[] = $route->toArray();
            }
        }

        // TODO figure out if it's worth using the file handler or switching to Flysystem or something else for file handling
        $file = new \SplFileObject($path, 'w');
        $file->fwrite("<?php\n\n// Generated file, do not modify!\n\nreturn " . var_export($routes, true) . ";\n");
    }
}
