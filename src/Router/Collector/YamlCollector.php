<?php
declare(strict_types=1);

namespace YapepBase\Router\Collector;

use Symfony\Component\Yaml\Yaml;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\File\IFileHandler;
use YapepBase\Router\Entity\Route;

class YamlCollector implements IRouteCollector
{
    /** @var IFileHandler */
    protected $fileHandler;
    /** @var string */
    protected $path;
    /** @var Route[] */
    protected $routes = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(IFileHandler $fileHandler, string $path)
    {
        $this->path        = $path;
        $this->fileHandler = $fileHandler;

        $this->populateRoutes();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function populateRoutes(): void
    {
        if (
            !$this->fileHandler->checkIsPathExists($this->path)
            || !$this->fileHandler->checkIsReadable($this->path)
        ) {
            throw new InvalidArgumentException('Path does not exist or not readable: ' . $this->path);
        }

        $parsed = Yaml::parse($this->fileHandler->getAsString($this->path));

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
