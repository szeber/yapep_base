<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Collector;

use Mockery\MockInterface;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\File\IFileHandler;
use YapepBase\Router\Collector\YamlCollector;
use YapepBase\Router\Entity\Path;
use YapepBase\Test\Unit\TestAbstract;

class YamlCollectorTest extends TestAbstract
{
    /** @var MockInterface */
    protected $fileHandler;
    /** @var string */
    protected $path = '/tmp/test.yaml';

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileHandler = \Mockery::mock(IFileHandler::class);
    }

    public function testConstructWhenPathNotExist_shouldThrowException()
    {
        $this->expectPathExistenceChecked(false);

        $this->expectException(InvalidArgumentException::class);
        $this->getYamlCollector();
    }

    public function testConstructWhenPathNotReadable_shouldThrowException()
    {
        $this->expectPathExistenceChecked(true);
        $this->expectPathReadabilityChecked(false);

        $this->expectException(InvalidArgumentException::class);
        $this->getYamlCollector();
    }

    public function testConstruct_shouldStoreRoutes()
    {
        $yaml = <<<YAML
- name: foo
  controller: Foo
  action: Index
  methods:
    - GET
    - POST
  paths:
    - pathPattern: /foo
YAML;

        $this->expectPathExistenceChecked(true);
        $this->expectPathReadabilityChecked(true);
        $this->expectGetFileContent($yaml);

        $collector = $this->getYamlCollector();

        $routes = $collector->getCollectedRoutes();

        $this->assertSame('foo', $routes[0]->getName());
        $this->assertSame('Foo', $routes[0]->getController());
        $this->assertSame('Index', $routes[0]->getAction());
        $this->assertSame(['GET', 'POST'], $routes[0]->getMethods());
    }

    protected function getYamlCollector(): YamlCollector
    {
        return new YamlCollector($this->fileHandler, $this->path);
    }

    protected function expectPathExistenceChecked(bool $expectedResult): void
    {
        $this->fileHandler
            ->shouldReceive('checkIsPathExists')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);
    }

    protected function expectPathReadabilityChecked(bool $expectedResult): void
    {
        $this->fileHandler
            ->shouldReceive('checkIsReadable')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);
    }

    protected function expectGetFileContent(string $expectedResult): void
    {
        $this->fileHandler
            ->shouldReceive('getAsString')
            ->once()
            ->with($this->path)
            ->andReturn($expectedResult);
    }
}
