<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class Meta extends BlockAbstract
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $content;

    public function __construct(string $name, string $content)
    {
        $this->name    = $name;
        $this->content = $content;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function appendContent(string $content)
    {
        $this->content .= $content;
    }

    protected function renderContent(): void
    {
        // ----------- HTML ------------
?>

<meta name="<?=$this->name ?>" content="<?=$this->content ?>" />

<?php
// ----------- /HTML ------------
    }
}
