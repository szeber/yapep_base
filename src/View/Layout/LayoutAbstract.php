<?php
declare(strict_types = 1);

namespace YapepBase\View\Layout;

use YapepBase\Exception\ParameterException;
use YapepBase\View\Block\BlockAbstract;
use YapepBase\View\Block\Html\CharsetMeta;
use YapepBase\View\Block\Html\Condition;
use YapepBase\View\Block\Html\CssFile;
use YapepBase\View\Block\Html\HttpMeta;
use YapepBase\View\Block\Html\JavaScriptFile;
use YapepBase\View\Block\Html\Link;
use YapepBase\View\Block\Html\Meta;
use YapepBase\View\Block\Html\Title;
use YapepBase\View\IRenderable;
use YapepBase\View\ViewAbstract;

/**
 * Layout for decorating the templates.
 */
abstract class LayoutAbstract extends ViewAbstract
{
    /** @var string */
    private $innerContent = '';

    /** @var Title */
    protected $title;

    /** @var CharsetMeta|null */
    protected $charsetMeta = null;

    /** @var Meta[] */
    protected $metas = [];

    /** @var HttpMeta[] */
    protected $httpMetas = [];

    /** @var Link[] */
    protected $links = [];

    /** @var Condition[] */
    protected $headerJavaScriptFiles = [];

    /** @var Condition[] */
    protected $footerJavaScriptFiles = [];

    /** @var Condition[] */
    protected $cssFiles = [];

    /** @var IRenderable[][] */
    protected $slotsByName = [];

    public function __construct()
    {
        $this->title = new Title();
    }

    public function setInnerContent(string $content): void
    {
        $this->innerContent = $content;
    }

    protected function renderInnerContent(): void
    {
        echo $this->innerContent;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function addMeta(Meta $meta, bool $overwrite = true): void
    {
        $metaName = $meta->getName();
        if ($overwrite || !isset($this->metas[$meta->getName()])) {
            $this->metas[$metaName] = $meta;
        } else {
            $this->metas[$metaName]->appendContent($meta->getContent());
        }
    }

    public function setCharsetMeta(CharsetMeta $charset): void
    {
        $this->charsetMeta = $charset;
    }

    protected function renderMetas(): void
    {
        if (!empty($this->charsetMeta)) {
            $this->charsetMeta->render();
        }

        foreach ($this->metas as $meta) {
            $meta->render();
        }
    }

    public function addHttpMeta(HttpMeta $meta, bool $overwrite = true)
    {
        $metaName = $meta->getName();
        if ($overwrite || !isset($this->httpMetas[$meta->getName()])) {
            $this->httpMetas[$metaName] = $meta;
        } else {
            $this->httpMetas[$metaName]->appendContent($meta->getContent());
        }
    }

    protected function renderHttpMetas(): void
    {
        foreach ($this->httpMetas as $meta) {
            $meta->render();
        }
    }

    public function addLink(Link $link): void
    {
        $this->links[] = $link;
    }

    protected function renderLinks(): void
    {
        $result = '';
        foreach ($this->links as $link) {
            $result .= (string)$link;
        }
        echo $result;
    }

    public function addHeaderJavaScriptFile(string $path, string $condition = ''): void
    {
        $this->addConditionalFile($this->headerJavaScriptFiles, new JavaScriptFile($path), $condition);
    }

    public function addFooterJavaScriptFile(string $path, string $condition = ''): void
    {
        $this->addConditionalFile($this->footerJavaScriptFiles, new JavaScriptFile($path), $condition);
    }

    public function addCss(string $path, string $condition = '', ?string $media = null): void
    {
        $this->addConditionalFile($this->cssFiles, new CssFile($path, $media), $condition);
    }

    protected function renderHeaderJavaScriptFiles(): void
    {
        foreach ($this->headerJavaScriptFiles as $condition) {
            $condition->render();
        }
    }

    protected function renderFooterJavaScriptFiles(): void
    {
        foreach ($this->footerJavaScriptFiles as $condition) {
            $condition->render();
        }
    }

    protected function renderStyleSheets(): void
    {
        foreach ($this->cssFiles as $condition) {
            $condition->render();
        }
    }

    /**
     * @param Condition[]   $files
     * @param BlockAbstract $fileBlock
     * @param string        $condition
     */
    protected function addConditionalFile(array &$files, IRenderable $fileBlock, string $condition = ''): void
    {
        if (!isset($files[$condition])) {
            $conditionObject = new Condition($condition);

            $files[$condition] = $conditionObject;
        }

        $files[$condition]->addElement($fileBlock);
    }

    public function addToSlot(string $name, IRenderable $slot): void
    {
        $this->slotsByName[$name][] = $slot;
    }

    /**
     * @throws ParameterException   If the given slot does not exist.
     */
    protected function renderSlot(string $name): void
    {
        if (!isset($this->slotsByName[$name])) {
            throw new ParameterException('Slot not defined: ' . $name);
        }

        foreach ($this->slotsByName[$name] as $renderable) {
            $renderable->render();
        }
    }
}
