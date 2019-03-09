<?php
declare(strict_types=1);

namespace YapepBase\View;

/**
 * ViewAbstract class what should be extended by every View class.
 */
abstract class ViewAbstract
{
    /** @var Data */
    private $data;

    /**
     * Does the actual rendering.
     */
    abstract protected function renderContent(): void;

    /**
     * Renders the view and prints it.
     */
    protected function render(): void
    {
        $this->renderContent();
    }

    /**
     * Returns the rendered content.
     */
    public function toString(): string
    {
        ob_start();
        $this->render();
        $result = ob_get_clean();

        return $result;
    }

    /**
     * Renders the given block
     */
    protected function renderBlock(BlockAbstract $block): void
    {
        // The View Object can have a layout, so we give it to the block as well to provide access
        if ($this instanceof IHasLayout && $this->checkHasLayout()) {
            $block->setLayout($this->getLayout());
        } // The current View Object is a Layout, so we pass it to the block as well
        elseif ($this instanceof LayoutAbstract) {
            $block->setLayout($this);
        }

        echo $block->toString();
    }

    public function setData(Data $data): void
    {
        $this->data = $data;
    }

    protected function getData(): Data
    {
        return $this->data;
    }
}
