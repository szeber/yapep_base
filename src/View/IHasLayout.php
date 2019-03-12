<?php
declare(strict_types = 1);

namespace YapepBase\View;

use YapepBase\View\Layout\LayoutAbstract;

/**
 * Interface which tells you that the implementing class can have a layout.
 */
interface IHasLayout
{
    public function setLayout(LayoutAbstract $layout): void;

    public function getLayout(): ?LayoutAbstract;

    public function hasLayout(): bool;
}
