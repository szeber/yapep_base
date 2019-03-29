<?php
declare(strict_types=1);

namespace YapepBase\Test\Integration\Router;

use Mockery\MockInterface;
use YapepBase\File\IFileHandler;
use YapepBase\Router\BasicRouter;
use YapepBase\Router\Collector\YamlCollector;
use YapepBase\Router\Exception\RouteNotFoundException;
use YapepBase\Test\Integration\TestAbstract;

class BasicRouterTest extends TestAbstract
{
    /** @var string */
    protected $yamlPath = '/tmp/test.yaml';
    /** @var MockInterface */
    protected $fileHandler;
    /** @var string */
    protected $simpleYamlRoute = <<<YAML
- name: foo
  controller: Foo
  action: Index
  methods:
    - GET
  paths:
    - pathPattern: /foo
YAML;

    /** @var string  */
    protected $multiPathYamlRoute = <<<YAML
- name: bar
  controller: Bar
  action: Index
  paths:
    - pathPattern: /bar
    - pathPattern: /bar/num/{id}
      params:
        - name: id
          paramClass: \YapepBase\Router\Entity\Param\Numeric
    - pathPattern: /bar/multi/{alpha}/{enum}
      params:
        - name: alpha
          paramClass: \YapepBase\Router\Entity\Param\Alpha
        - name: enum
          paramClass: \YapepBase\Router\Entity\Param\Enum
          values: [one, two]
YAML;


    protected function setUp(): void
    {
        parent::setUp();
        $this->fileHandler = \Mockery::mock(IFileHandler::class);
    }

    public function testGetControllerActionByMethodAndPathWhenWhenMethodMatches_shouldFindTarget()
    {

        $basicRouter = $this->getRouterByYamlContent($this->simpleYamlRoute);

        $controllerAction = $basicRouter->getControllerActionByMethodAndPath('GET', '/foo');

        $this->assertSame('Foo', $controllerAction->getController());
        $this->assertSame('Index', $controllerAction->getAction());
    }

    public function testGetControllerActionByMethodAndPathWhenWhenMethodDoesNotMatch_shouldThrowException()
    {
        $basicRouter = $this->getRouterByYamlContent($this->simpleYamlRoute);

        $this->expectException(RouteNotFoundException::class);
        $basicRouter->getControllerActionByMethodAndPath('POST', '/foo');
    }

    public function multiPathRoutePathProvider(): array
    {
        $controller = 'Bar';
        $action     = 'Index';
        return [
            ['/bar', $controller, $action],
            ['/bar/num/1', $controller, $action],
            ['/bar/multi/abc/one', $controller, $action],
        ];
    }

    /**
     * @dataProvider multiPathRoutePathProvider
     */
    public function testGetControllerActionByMethodAndPathWhenWhenMultiPathRouteGiven_shouldFindTargetByEveryPath(
        string $path, string $expectedController, string $expectedAction
    )
    {
        $basicRouter = $this->getRouterByYamlContent($this->multiPathYamlRoute);

        $controllerAction = $basicRouter->getControllerActionByMethodAndPath('GET', $path);

        $this->assertSame($expectedController, $controllerAction->getController());
        $this->assertSame($expectedAction, $controllerAction->getAction());
    }

    public function testGetPathByNameWhenSimpleRouteGiven_shouldReturnOnePath()
    {
        $basicRouter = $this->getRouterByYamlContent($this->simpleYamlRoute);

        $path = $basicRouter->getPathByName('foo');

        $this->assertSame('/foo', $path);
    }

    public function multiPathRouteNameProvider(): array
    {
        $routeName = 'bar';
        return [
            [$routeName, [], '/bar'],
            [$routeName, ['id' => 1], '/bar/num/1'],
            [$routeName, ['alpha' => 'abc', 'enum' => 'one'], '/bar/multi/abc/one'],
        ];
    }

    /**
     * @dataProvider multiPathRouteNameProvider
     */
    public function testGetPathByNameWhenMultiRouteGiven_shouldReturnPathAccordingToParam(string $routeName, array $params, string $expectedPath)
    {
        $basicRouter = $this->getRouterByYamlContent($this->multiPathYamlRoute);

        $path = $basicRouter->getPathByName($routeName, $params);

        $this->assertSame($expectedPath, $path);
    }

    protected function getRouterByYamlContent(string $yamlContent): BasicRouter
    {
        $this->expectYamlFileContains($yamlContent);
        $yamlCollector = new YamlCollector($this->fileHandler, $this->yamlPath);

        return new BasicRouter($yamlCollector->getCollectedRoutes());
    }

    protected function expectYamlFileContains(string $yamlContent): void
    {
        $this->fileHandler
            ->shouldReceive('checkIsPathExists')
                ->andReturn(true)
                ->getMock()
            ->shouldReceive('checkIsReadable')
                ->andReturn(true)
                ->getMock()
            ->shouldReceive('getAsString')
                ->andReturn($yamlContent);
    }
}
