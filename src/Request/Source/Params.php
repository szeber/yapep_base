<?php
declare(strict_types=1);

namespace YapepBase\Request\Source;

use YapepBase\Application;
use YapepBase\Helper\ArrayHelper;

class Params implements ISource
{
    /** @var array  */
    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function toArray(): array
    {
        return $this->params;
    }

    public function has(string $name): bool
    {
        return isset($this->params[$name]);
    }

    public function getAsInt(string $name, ?int $default = null):? int
    {
        return $this->getArrayHelper()->getElementAsInt($this->params, $name, $default);
    }

    public function getAsString(string $name, ?string $default = null):? string
    {
        return $this->getArrayHelper()->getElementAsString($this->params, $name, $default);
    }

    public function getAsFloat(string $name, ?float $default = null):? float
    {
        return $this->getArrayHelper()->getElementAsFloat($this->params, $name, $default);
    }

    public function getAsArray(string $name, ?array $default = []):? array
    {
        return $this->getArrayHelper()->getElementAsArray($this->params, $name, $default);
    }

    public function getAsBool(string $name, ?bool $default = null):? bool
    {
        return $this->getArrayHelper()->getElementAsBool($this->params, $name, $default);
    }

    protected function getArrayHelper(): ArrayHelper
    {
        return Application::getInstance()->getDiContainer()->get(ArrayHelper::class);
    }
}
