<?php
declare(strict_types=1);

namespace YapepBase\View;

interface IRenderable
{
    /**
     * Renders the object and sends the content to output.
     */
    public function render(): void;

    /**
     * Renders the object and returns the content
     */
    public function __toString(): string;
}
