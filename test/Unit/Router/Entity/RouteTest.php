<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Path;
use YapepBase\Router\Entity\Route;
use YapepBase\Router\IAnnotation;
use YapepBase\Test\Unit\TestAbstract;

class RouteTest extends TestAbstract
{
    /** @var string */
    protected $controller    = 'Controller';
    /** @var string */
    protected $action        = 'Action';
    /** @var string */
    protected $name          = 'routeName';
    /** @var array */
    protected $methods       = ['GET'];
    /** @var array */
    protected $regexPatterns = [];
    /** @var Path[] */
    protected $paths         = [];
    /** @var IAnnotation[] */
    protected $annotations   = [];

    protected function setUp(): void
    {
        parent::setUp();

        $path                  = new Path('/', []);
        $this->paths[]         = $path;
        $this->regexPatterns[] = $path->getRegexPattern();
    }

    public function testConstruct_shouldStoreGivenValuesProperly()
    {
        $route = $this->getRoute();

        $this->assertRouteEquals($route);
    }

    public function testSetState_shouldReturnProperlySetRouter()
    {
        $state = [
            Route::KEY_CONTROLLER     => $this->controller,
            Route::KEY_ACTION         => $this->action,
            Route::KEY_NAME           => $this->name,
            Route::KEY_METHODS        => $this->methods,
            Route::KEY_REGEX_PATTERNS => $this->regexPatterns,
            Route::KEY_PATHS          => $this->paths,
            Route::KEY_ANNOTATIONS    => $this->annotations,
        ];
        $route = Route::__set_state($state);

        $this->assertRouteEquals($route);
    }

    public function testCreateFromArray_shouldReturnProperlySetRouter()
    {
        $routeArray     = $this->getRoute()->toArray();
        $routeFromArray = Route::createFromArray($routeArray);

        $this->assertRouteEquals($routeFromArray);
    }

    public function invalidRouteArrayProvider()
    {
        $routeArray = $this->getRoute()->toArray();

        return [
            'empty array'           => [[], 'The route array is empty'],
            'controller empty'      => [array_merge($routeArray, [Route::KEY_CONTROLLER => '']), 'No controller is specified for route'],
            'action empty'          => [array_merge($routeArray, [Route::KEY_ACTION => '']), 'No action is specified for route'],
            'methods not array'     => [array_merge($routeArray, [Route::KEY_METHODS => '']), 'The methods should be an array in the route'],
            'patterns not array'    => [array_merge($routeArray, [Route::KEY_REGEX_PATTERNS => '']), 'The regexPatterns should be an array in the route'],
            'empty paths'           => [array_merge($routeArray, [Route::KEY_PATHS => '']), 'No paths specified or the path is not an array for route'],
            'annotations not array' => [array_merge($routeArray, [Route::KEY_ANNOTATIONS => '']), 'Annotations should be an array in the route'],
        ];
    }

    /**
     * @dataProvider invalidRouteArrayProvider
     */
    public function testCreateFromArrayWhenInvalidArrayGiven_shouldThrowException(array $array, string $expectedExceptionMessage)
    {
        $this->expectExceptionObject(new InvalidArgumentException($expectedExceptionMessage));
        Route::createFromArray($array);
    }

    public function testMatchMethodAndPathWhenMethodDifferent_shouldReturnNull()
    {
        $result = $this->getRoute()->matchMethodAndPath('different', 'path');
        $this->assertNull($result);
    }

    public function testMatchMethodAndPathWhenPathDoesNotMatch_shouldReturnNull()
    {
        $result = $this->getRoute()->matchMethodAndPath($this->methods[0], 'path');
        $this->assertNull($result);
    }

    public function testMatchMethodAndPathWhenMatches_shouldReturnControllerAction()
    {
        $result = $this->getRoute()->matchMethodAndPath($this->methods[0], $this->paths[0]->getParameterisedPath([]));

        $this->assertSame($this->controller, $result->getController());
        $this->assertSame($this->action, $result->getAction());
    }

    public function testGetParameterisedPathWhenParamsDoesNotExist_shouldReturnNull()
    {
        $result = $this->getRoute()->getParameterisedPath(['test' => 1]);

        $this->assertNull($result);
    }

    public function testGetParameterisedPath_shouldReturnParameterisedPath()
    {
        $routeParams = [];

        $result = $this->getRoute()->getParameterisedPath($routeParams);

        $this->assertSame($this->paths[0]->getParameterisedPath($routeParams), $result);
    }

    protected function getRoute(): Route
    {
        return new Route(
            $this->controller,
            $this->action,
            $this->name,
            $this->methods,
            $this->regexPatterns,
            $this->paths,
            $this->annotations
        );
    }

    protected function assertRouteEquals(Route $actualRoute)
    {
        $this->assertSame($this->controller, $actualRoute->getController());
        $this->assertSame($this->action, $actualRoute->getAction());
        $this->assertSame($this->name, $actualRoute->getName());
        $this->assertSame($this->methods, $actualRoute->getMethods());
        $this->assertSame($this->regexPatterns, $actualRoute->getRegexPatterns());
        $this->assertEquals($this->paths, $actualRoute->getPaths());
        $this->assertEquals($this->annotations, $actualRoute->getAnnotations());
    }
}
