<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

/**
 * Stores and handles the custom parameters such as params coming from the route or from CLI
 */
class CustomParams extends Params implements ICustomParams
{
    public function set(string $name, $value): void
    {
        $this->params[$name] = $value;
    }
}
