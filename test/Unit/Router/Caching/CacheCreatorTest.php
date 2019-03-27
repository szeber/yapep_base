<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Caching;

use YapepBase\File\IFileHandler;
use YapepBase\Router\Caching\CacheCreator;
use YapepBase\Router\Collector\IRouteCollector;
use YapepBase\Test\Unit\TestAbstract;

class CacheCreatorTest extends TestAbstract
{
    /** @var string */
    protected $path = '/tmp/cache.php';

    public function testGenerateCache_shouldGenerateFileProperly()
    {
        $collector1          = $this->expectCollectorUsed([1]);
        $collector2          = $this->expectCollectorUsed([2]);
        $expectedFileContent = <<<'PHP'
<?php

// Generated file, do not modify!

return array (
  0 => 1,
  1 => 2,
);
PHP;
        $fileHandler = $this->expectWriteToFile($expectedFileContent);

        $cacheCreator = new CacheCreator($fileHandler);
        $cacheCreator->addCollector($collector1);
        $cacheCreator->addCollector($collector2);

        $cacheCreator->generateCache($this->path);
    }

    protected function expectWriteToFile(string $expectedContent): IFileHandler
    {
        return \Mockery::mock(IFileHandler::class)
            ->shouldReceive('write')
            ->once()
            ->with($this->path, $expectedContent)
            ->getMock();
    }

    protected function expectCollectorUsed(array $expectedRoutes): IRouteCollector
    {
        return \Mockery::mock(IRouteCollector::class)
            ->shouldReceive('getCollectedRoutes')
            ->once()
            ->andReturn($expectedRoutes)
            ->getMock();
    }
}
