<?php
declare(strict_types=1);

namespace YapepBase\View\Block;


use YapepBase\View\IHasLayout;
use YapepBase\View\Layout\LayoutAbstract;
use YapepBase\View\ViewAbstract;

/**
 * BlockAbstract class, should be extended by every Block.
 */
abstract class BlockAbstract extends ViewAbstract implements IHasLayout
{

    /** @var LayoutAbstract|null */
    protected $layout;

    public function setLayout(LayoutAbstract $layout): void
    {
        $this->layout = $layout;
    }

    public function getLayout(): LayoutAbstract
    {
        return $this->layout;
    }

    public function checkHasLayout(): bool
    {
        return !is_null($this->layout);
    }
}
