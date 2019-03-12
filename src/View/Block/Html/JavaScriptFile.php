<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class JavaScriptFile extends BlockAbstract
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    protected function renderContent(): void
    {
        // ----------- HTML ------------
?>

<script type="text/javascript" src="<?=$this->path ?>"></script>

<?php
// ----------- /HTML ------------
    }
}
