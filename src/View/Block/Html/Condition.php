<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;
use YapepBase\View\IRenderable;

class Condition extends BlockAbstract
{
    /** @var ?string */
    protected $condition;

    /** @var IRenderable[] */
    protected $elements = [];

    public function __construct(?string $condition = null)
    {
        $this->condition = $condition;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /**
     * @return IRenderable[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function addElement(IRenderable $element): void
    {
        $this->elements[] = $element;
    }

    protected function renderContent(): void
    {
        if (empty($this->condition)) {
            $this->renderElements();
        } else {
            $this->renderElementsInCondition();
        }
    }

    protected function renderElementsInCondition()
    {
        // ----------- HTML ------------
?>
<!--[if <?=$this->condition ?>]>
    <?php $this->renderElements(); ?>

<![endif]-->
<?php
// ----------- /HTML ------------
    }

    protected function renderElements(): void
    {
        foreach ($this->elements as $element) {
            $element->render();
        }
    }
}
