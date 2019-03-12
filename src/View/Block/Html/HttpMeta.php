<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

class HttpMeta extends Meta
{
    protected function renderContent(): void
    {
        // ----------- HTML ------------
?>

<meta http-equiv="<?=$this->name ?>" content="<?=$this->content ?>" />

<?php
// ----------- /HTML ------------
    }
}
