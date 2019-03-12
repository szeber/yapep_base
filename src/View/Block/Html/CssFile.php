<?php
declare(strict_types = 1);

namespace YapepBase\View\Block\Html;

class CssFile extends Link
{
    /** @var string|null */
    protected $media;

    public function __construct(string $path, ?string $media = null)
    {
        parent::__construct($path, 'stylesheet');

        $this->setType('text/css')
            ->setMedia($media);
    }
}
