<?php
declare(strict_types=1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class CssFile extends BlockAbstract
{
    /** @var string */
    protected $path;

    /** @var string|null */
    protected $media;

    public function __construct(string $path, ?string $media = null)
    {
        $this->path  = $path;
        $this->media = $media;
    }

    protected function renderContent(): void
    {
// ----------- HTML ------------
?>

<link
    rel="stylesheet"
    type="text/css"
    href="<?=$this->path ?>"
<?php if (!empty($this->media)): ?>
    media="<?=$this->media ?>"
<?php endif; ?>
/>

<?php
// ----------- /HTML ------------
    }
}
