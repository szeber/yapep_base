<?php
declare(strict_types=1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class Condition extends BlockAbstract
{
    /** @var ?string */
    protected $condition;

    /** @var BlockAbstract[] */
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
     * @return BlockAbstract[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function addElement(BlockAbstract $file): void
    {
        $this->elements[] = $file;
    }

    protected function renderContent(): void
    {
        if (empty($this->condition)) {
            $this->renderFiles();
        }
        else {
            $this->renderFilesInCondition();
        }
    }

    protected function renderFilesInCondition()
    {
// ----------- HTML ------------
?>
<!--[if <?=$this->condition ?>]>
    <?php $this->renderFiles(); ?>
<![endif]-->
<?php
// ----------- /HTML ------------

    }

    protected function renderFiles(): void
    {
        foreach ($this->elements as $file) {
            $file->render();
        }
    }
}
