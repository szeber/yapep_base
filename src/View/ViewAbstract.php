<?php
declare(strict_types=1);

namespace YapepBase\View;

use YapepBase\Application;
use YapepBase\View\Data\ICanEscape;

/**
 * ViewAbstract class what should be extended by every View class.
 */
abstract class ViewAbstract implements IRenderable
{
    /** @var ICanEscape */
    private $data;

    /**
     * Does the actual rendering.
     */
    abstract protected function renderContent(): void;

    public function render(): void
    {
        $this->renderContent();
    }

    public function __toString(): string
    {
        ob_start();
        $this->render();
        $result = ob_get_clean();

        return $result;
    }

    protected function setData(ICanEscape $data): void
    {
        $this->data = $data;
    }

    protected function getData(): ICanEscape
    {
        if (empty($this->data)) {
            $this->data = Application::getInstance()->getDiContainer()->getViewData();
        }

        return $this->data;
    }
}
