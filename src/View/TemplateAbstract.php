<?php
declare(strict_types=1);

namespace YapepBase\View;

/**
 * Template class. Represents the content of the page.
 */
abstract class TemplateAbstract extends ViewAbstract implements IHasLayout
{
    /**
     * @var LayoutAbstract|null
     */
    protected $layout;

    public function setLayout(LayoutAbstract $layout): void
    {
        $this->layout = $layout;
    }

    public function getLayout(): ?LayoutAbstract
    {
        return $this->layout;
    }

    public function checkHasLayout(): bool
    {
        return !is_null($this->layout);
    }

    public function render(): void
    {
        if ($this->layout !== null) {
            ob_start();
            parent::render();
            $content = ob_get_clean();
            $this->layout->setInnerContent($content);
            $this->layout->render();
        } else {
            parent::render();
        }
    }
}
