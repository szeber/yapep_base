<?php
declare(strict_types=1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class Title extends BlockAbstract
{
    /** @var array */
    protected $titleParts = [];
    /** @var string */
    protected $separator = ' - ';

    public function setTitle(string $title): void
    {
        $this->titleParts = [$title];
    }

    public function getTitle(): string
    {
        return implode($this->separator, $this->titleParts);
    }

    public function getTitleParts(): array
    {
        return $this->titleParts;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    public function prependToTitle(string $titlePart): void
    {
        array_unshift($this->titleParts, $titlePart);
    }

    public function appendToTitle(string $titlePart): void
    {
        $this->titleParts[] = $titlePart;
    }

    protected function renderContent(): void
    {
        if (empty($this->titleParts)) {
            return;
        }
// ----------- HTML ------------
?>

<title><?=$this->getTitle() ?></title>

<?php
// ----------- /HTML ------------
    }

}
