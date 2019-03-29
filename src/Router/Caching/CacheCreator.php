<?php
declare(strict_types=1);

namespace YapepBase\Router\Caching;

use YapepBase\Exception\File\Exception;
use YapepBase\File\IFileHandler;
use YapepBase\Router\Collector\IRouteCollector;

class CacheCreator
{
    /** @var IRouteCollector[] */
    protected $collectors = [];
    /** @var IFileHandler */
    protected $fileHandler;

    public function __construct(IFileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function addCollector(IRouteCollector $collector): void
    {
        $this->collectors[] = $collector;
    }

    /**
     * @throws Exception
     */
    public function generateCache(string $path): void
    {
        $routes = [];

        foreach ($this->collectors as $collector) {
            $routes = array_merge($routes, $collector->getCollectedRoutes());
        }

        $fileContent = "<?php\n\n// Generated file, do not modify!\n\nreturn " . var_export($routes, true) . ';';

        $this->fileHandler->write($path, $fileContent);
    }
}
