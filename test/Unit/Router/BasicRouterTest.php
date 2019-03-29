<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router;

use YapepBase\Request\IRequest;
use YapepBase\Router\BasicRouter;
use YapepBase\Router\Entity\Param\Numeric;
use YapepBase\Router\Entity\Path;
use YapepBase\Router\Entity\Route;
use YapepBase\Router\Exception\RouteNotFoundException;
use YapepBase\Test\Unit\TestAbstract;

class BasicRouterTest extends TestAbstract
{
    /** @var string */
    protected $controller = 'IndexController';
    /** @var string */
    protected $action = 'GetUser';
    /** @var string */
    protected $routeName = 'get-user';
    /** @var string */
    protected $pathPattern = '/test/{id}';
    /** @var string */
    protected $pathParamName = 'id';
    /** @var int */
    protected $pathParamValue = 12;
    /** @var string */
    protected $parameterisedPath = '/test/12';

    public function testGetPathByControllerAndActionWhenNotRegistered_shouldThrowException()
    {
        $router = new BasicRouter([]);

        $this->expectException(RouteNotFoundException::class);
        $router->getPathByControllerAndAction($this->controller, $this->action);
    }

    public function testGetPathByControllerAndAction_shouldReturnParameterisedPath()
    {
        $router = new BasicRouter([$this->getRoute()]);

        $routeParams = [$this->pathParamName => $this->pathParamValue];
        $path        = $router->getPathByControllerAndAction($this->controller, $this->action, $routeParams);

        $this->assertSame($this->parameterisedPath, $path);
    }

    public function testGetPathByNameWhenNotRegistered_shouldThrowException()
    {
        $router = new BasicRouter([]);

        $this->expectException(RouteNotFoundException::class);
        $router->getPathByName('path');
    }

    public function testGetPathByName_shouldReturnParameterisedPath()
    {
        $router = new BasicRouter([$this->getRoute()]);

        $routeParams = [$this->pathParamName => $this->pathParamValue];
        $path        = $router->getPathByName($this->routeName, $routeParams);

        $this->assertSame($this->parameterisedPath, $path);
    }

    public function testGetControllerActionByMethodAndPathWhenRouteNotFound_shouldThrowException()
    {
        $router = new BasicRouter([$this->getRoute()]);

        $this->expectException(RouteNotFoundException::class);
        $router->getControllerActionByMethodAndPath('GET', '/nonExistent');
    }

    public function testGetControllerActionByMethodAndPath_shouldReturnControllerAction()
    {
        $router = new BasicRouter([$this->getRoute()]);

        $controllerAction = $router->getControllerActionByMethodAndPath('GET', $this->parameterisedPath);

        $this->assertSame($this->controller, $controllerAction->getController());
        $this->assertSame($this->action, $controllerAction->getAction());
    }

    public function testGetControllerActionByRequest_shouldReturnControllerAction()
    {
        $request = $this->expectRequestUsedToGetRoute();
        $router  = new BasicRouter([$this->getRoute()]);

        $controllerAction = $router->getControllerActionByRequest($request);

        $this->assertSame($this->controller, $controllerAction->getController());
        $this->assertSame($this->action, $controllerAction->getAction());
    }

    protected function getRoute()
    {
        $path = new Path($this->pathPattern, [new Numeric($this->pathParamName)]);

        return new Route($this->controller, $this->action, $this->routeName, ['GET'], [$path], []);
    }

    protected function expectRequestUsedToGetRoute(): IRequest
    {
        return \Mockery::mock(IRequest::class)
            ->shouldReceive('getMethod')
                ->once()
                ->andReturn('GET')
                ->getMock()
            ->shouldReceive('getTarget')
                ->once()
                ->andReturn($this->parameterisedPath)
                ->getMock();
    }
}
