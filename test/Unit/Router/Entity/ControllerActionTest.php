<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\ControllerAction;
use YapepBase\Router\IAnnotation;
use YapepBase\Test\Unit\TestAbstract;

class ControllerActionTest extends TestAbstract
{
    /** @var string */
    protected $controller  = 'Controller';
    /** @var string */
    protected $action      = 'Action';
    /** @var array */
    protected $parameters  = ['test' => 1];
    /** @var IAnnotation[] */
    protected $annotations = [];
    /** @var string */
    protected $annotationClassName = 'annotation';

    protected function setUp(): void
    {
        parent::setUp();
        $this->annotations[$this->annotationClassName] = \Mockery::mock(IAnnotation::class);
    }

    public function testConstructWhenParametersWithNumericIndexesGiven_shouldThrowException()
    {
        $invalidParams = [0 => 1];

        $this->expectException(InvalidArgumentException::class);
        new ControllerAction($this->controller, $this->action, $invalidParams, $this->annotations);
    }

    public function testConstructWhenInvalidAnnotationsGiven_shouldThrowException()
    {
        $invalidAnnotations = [0 => 1];

        $this->expectException(InvalidArgumentException::class);
        new ControllerAction($this->controller, $this->action, $this->parameters, $invalidAnnotations);
    }

    public function testGetControllerAction_shouldReturnIdentifierString()
    {
        $result = $this->getControllerAction()->getControllerAction();

        $this->assertSame('Controller/Action', $result);
    }

    public function testToString_shouldReturnIdentifierString()
    {
        $result = (string)$this->getControllerAction();

        $this->assertSame('Controller/Action', $result);
    }

    public function testGetAnnotationWhenExists_shouldReturnAnnotation()
    {
        $annotation = $this->getControllerAction()->getAnnotation($this->annotationClassName);

        $this->assertSame($this->annotations[$this->annotationClassName], $annotation);
    }

    public function testGetAnnotationWhenNotExists_shouldReturnNull()
    {
        $annotation = $this->getControllerAction()->getAnnotation('notExist');

        $this->assertNull($annotation);
    }

    protected function getControllerAction(): ControllerAction
    {
        return new ControllerAction($this->controller, $this->action, $this->parameters, $this->annotations);
    }
}
