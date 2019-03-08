<?php
declare(strict_types=1);

namespace YapepBase\View;

/**
 * Template class. Represents the content of the page.
 */
abstract class TemplateAbstract extends ViewAbstract implements IHasLayout
{
    /**
     * @var LayoutAbstract
     */
    protected $layout = null;

    public function setLayout(LayoutAbstract $layout): void
    {
        $this->layout = $layout;
    }

    public function getLayout(): LayoutAbstract
    {
        return $this->layout;
    }

    /**
     * Checks if the object has a layout set.
     *
     * @return bool   TRUE if it has, FALSE otherwise.
     */
    public function checkHasLayout()
    {
        return !is_null($this->layout);
    }

    /**
     * Renders the view and prints it.
     *
     * @return void
     */
    public function render()
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
