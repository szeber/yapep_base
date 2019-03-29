<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\IAnnotation;

class ControllerAction
{
    /** @var string */
    protected $controller;
    /** @var string */
    protected $action;
    /** @var array */
    protected $paramsByName = [];
    /** @var IAnnotation[] */
    protected $annotationsByClassName = [];

    /**
     * @param string        $controller
     * @param string        $action
     * @param array         $paramsByName
     * @param IAnnotation[] $annotationsByClassName
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $controller, string $action, array $paramsByName, array $annotationsByClassName)
    {
        $this->validateParams($paramsByName);
        $this->validationAnnotations($annotationsByClassName);

        $this->controller             = $controller;
        $this->action                 = $action;
        $this->paramsByName           = $paramsByName;
        $this->annotationsByClassName = $annotationsByClassName;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validateParams(array $params): void
    {
        foreach ($params as $index => $value) {
            if (is_numeric($index)) {
                throw new InvalidArgumentException('Params should be indexed by the name.');
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validationAnnotations(array $annotations): void
    {
        foreach ($annotations as $annotation) {
            if (!($annotation instanceof IAnnotation)) {
                throw new InvalidArgumentException('Only Annotations should be passed');
            }
        }
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getControllerAction(): string
    {
        return $this->getController() . '/' . $this->getAction();
    }

    public function getParams(): array
    {
        return $this->paramsByName;
    }

    /**
     * @return IAnnotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotationsByClassName;
    }

    public function getAnnotation(string $className): ?IAnnotation
    {
        return $this->annotationsByClassName[$className] ?? null;
    }

    public function __toString()
    {
        return $this->getControllerAction();
    }
}
