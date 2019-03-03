<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject;

class ControllerAction
{
    /** @var string */
    protected $controller;
    /** @var string */
    protected $action;
    /** @var array */
    protected $parameters;

    public function __construct(string $controller, string $action, array $parameters)
    {
        $this->controller = $controller;
        $this->action     = $action;
        $this->parameters = $this->cleanupParameters($parameters);
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

    public function __toString()
    {
        return $this->getControllerAction();
    }

}
