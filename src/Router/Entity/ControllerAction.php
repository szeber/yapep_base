<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity;

use YapepBase\Router\IAnnotation;

class ControllerAction
{
    /** @var string */
    protected $controller;
    /** @var string */
    protected $action;
    /** @var array */
    protected $parameters = [];
    /** @var IAnnotation[] */
    protected $annotations = [];

    public function __construct(string $controller, string $action, array $parameters, array $annotations)
    {
        $this->controller  = $controller;
        $this->action      = $action;
        $this->parameters  = $this->cleanupParameters($parameters);
        $this->annotations = $annotations;
    }

    protected function cleanupParameters(array $parameters): array
    {
        foreach ($parameters as $index => $value) {
            if (is_numeric($index)) {
                unset($parameters[$index]);
            }
        }

        return $parameters;
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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return IAnnotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function getAnnotation(string $className): ?IAnnotation
    {
        return $this->annotations[$className] ?? null;
    }

    public function __toString()
    {
        return $this->getControllerAction();
    }
}
