<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

use YapepBase\View\Block\BlockAbstract;

/**
 * Class represents a link tag
 */
class Link extends BlockAbstract
{
    /** @var string */
    protected $href;

    /** @var string */
    protected $relationship;

    /** @var string|null */
    protected $media;

    /** @var string|null */
    protected $type;

    /** @var string|null */
    protected $title;

    public function __construct(string $href, string $relationship)
    {
        $this->href         = $href;
        $this->relationship = $relationship;
    }

    protected function renderContent(): void
    {
        // ----------- HTML ------------
?>
<link
    rel="<?= $this->relationship ?>"
<?php if (!empty($this->type)): ?>
    type="<?= $this->type ?>"
<?php endif; ?>
    href="<?= $this->href ?>"
<?php if (!empty($this->title)): ?>
    title="<?= $this->title ?>"
<?php endif; ?>
<?php if (!empty($this->media)): ?>
    media="<?= $this->media ?>"
<?php endif; ?>
/>

<?php
// ----------- /HTML ------------
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function setHref(?string $href): self
    {
        $this->href = $href;

        return $this;
    }

    public function getRelationship(): string
    {
        return $this->relationship;
    }

    public function setRelationship(string $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
