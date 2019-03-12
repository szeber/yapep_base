<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

class CharsetMeta extends BlockAbstract
{
    /** @var string */
    protected $charset;

    public function __construct(string $charset)
    {
        $this->charset = $charset;
    }

    protected function renderContent(): void
    {
        // ----------- HTML ------------
?>

<meta charset="<?=$this->charset ?>" />

<?php
// ----------- /HTML ------------
    }
}
