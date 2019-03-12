<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\View;

/**
 * Layout for decorating the templates.
 */
abstract class LayoutAbstract extends ViewAbstract
{
    /** The index which stores the path of the displayed css. */
    const STYLESHEET_INDEX_FILE = 'file';
    /** The index which stores the target media of the displayed css. */
    const STYLESHEET_INDEX_MEDIA = 'media';

    /**
     * Stores the inner content what decorated by the layout.
     *
     * @var string
     */
    private $innerContent = '';

    /**
     * The title of the page. The content will be concatenated with the title separator.
     *
     * @var array
     */
    protected $title = [];

    /**
     * The string what separates the title parts.
     *
     * @var string
     */
    protected $titleSeparator = ' - ';

    /**
     * Meta tags of the page.
     *
     * @var array
     */
    protected $metas = [];

    /**
     * Charset meta tag of the page.
     *
     * @var string
     */
    protected $charset = null;

    /**
     * HTTP meta tags of the page.
     *
     * @var array
     */
    protected $httpMetas = [];

    /**
     * Link tags of the page.
     *
     * @var array
     */
    protected $links = [];

    /**
     * Javascript files which are should be included in the setHeader.
     *
     * @var array
     */
    protected $headerJavaScripts = [];

    /**
     * Javascript files which are should be included in the footer.
     *
     * @var array
     */
    protected $footerJavaScripts = [];

    /**
     * Javascript variable declarations that should be set in the footer.
     *
     * @var array
     */
    protected $footerJavaScriptvariables = [];

    /**
     * CSS files used on the page.
     *
     * @var array
     */
    protected $styleSheets = [];

    /**
     * Name of the slots are being processed at the moment.
     *
     * @var array
     */
    protected $activeSlotNames = [];

    /**
     * Content of the slots which can be filled dynamically
     *
     * @var array
     */
    protected $slots = [];

    /**
     * Sets the inner content to be decorated by the layout.
     *
     * @param string $content   The inner content.
     *
     * @return void
     */
    public function setInnerContent($content)
    {
        $this->innerContent = $content;
    }

    /**
     * Displays the inner content.
     *
     * @return void
     */
    protected function renderInnerContent()
    {
        echo $this->innerContent;
    }

    /**
     * Sets the title separator.
     *
     * @param string $separator   The separator which will be used for separating the title parts.
     *
     * @return void
     */
    public function setTitleSeparator($separator)
    {
        $this->titleSeparator = $separator;
    }

    /**
     * Concatenates the given string to the beginning of the title.
     *
     * @param string $text   The string should be concatenated.
     *
     * @return void
     */
    public function prependToTitle($text)
    {
        array_unshift($this->title, $text);
    }

    /**
     * Concatenates the given string to the end of the title.1
     *
     * @param string $text   The string should be concatenated.
     *
     * @return void
     */
    public function appendToTitle($text)
    {
        $this->title[] = $text;
    }

    /**
     * Sets the title of the page.
     *
     * @param string $title   The new title.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = [$title];
    }

    /**
     * Returns the current title.
     *
     * @param bool $isString   If TRUE a string title will be returned concatenated with the separator,
     *                         If FALSE, an array will be returned which holds all parts of the title in order.
     *
     * @return string|array
     */
    public function getTitle($isString = false)
    {
        return $isString
            ? implode($this->titleSeparator, $this->title)
            : $this->title;
    }

    /**
     * Displays the title of the page in HTML format. (<var>title</var> tag).
     *
     * @return void
     */
    protected function renderTitle()
    {
        echo '<title>' . $this->getTitle(true) . '</title>';
    }

    /**
     * Adds (or overwrites) a meta tag to the page.
     *
     * @param string $name        The name of the meta tag.
     * @param string $content     The content of the meta tag.
     * @param bool   $overwrite   If TRUE it will overwrites the existing meta tag.
     *
     * @return void
     */
    public function addMeta($name, $content, $overwrite = true)
    {
        if ($overwrite || !isset($this->metas[$name])) {
            $this->metas[$name] = $content;
        } else {
            $this->metas[$name] .= $content;
        }
    }

    /**
     * Sets the charset meta tag to the page.
     *
     * @param string $charset   The charset of the meta tag.
     *
     * @return void
     */
    public function setCharsetMeta($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Displays the meta tags in HTML format.
     *
     * @return void
     */
    protected function renderMetas()
    {
        $result = '';

        if (!empty($this->charset)) {
            $result .= '<meta charset="' . $this->charset . '" />' . "\n";
        }

        foreach ($this->metas as $name => $content) {
            $result .= '<meta name="' . $name . '" content="' . $content . '" />' . "\n";
        }
        echo $result;
    }

    /**
     * Adds (or overwrites) a HTTP meta tag to the page.
     *
     * @param string $name        The name of the HTTP meta tag.
     * @param string $content     The content of the HTTP meta tag.
     * @param bool   $overwrite   If TRUE it will overwrites the existing HTTP meta tag.
     *
     * @return void
     */
    public function addHttpMeta($name, $content, $overwrite = true)
    {
        if ($overwrite || !isset($this->httpMetas[$name])) {
            $this->httpMetas[$name] = $content;
        } else {
            $this->httpMetas[$name] .= $content;
        }
    }

    /**
     * Displays the HTTP meta tags in HTML format.
     *
     * @return void
     */
    protected function renderHttpMetas()
    {
        $result = '';
        foreach ($this->httpMetas as $name => $content) {
            $result .= '<meta http-equiv="' . $name . '" content="' . $content . '" />' . "\n";
        }
        echo $result;
    }

    /**
     * Adds a link to the page.
     *
     * @param string $href    The <var>href</var> property of the tag.
     * @param string $rel     The <var>rel</var> property of the tag.
     * @param string $type    The type of the tag.
     * @param string $title   The title of the tag.
     *
     * @return void
     */
    public function addLink($href, $rel, $type = '', $title = '')
    {
        $this->links[] = ['href' => $href, 'rel' => $rel, 'type' => $type, 'title' => $title];
    }

    /**
     * Displays the links in HTML format.
     *
     * @return void
     */
    protected function renderLinks()
    {
        $result = '';
        foreach ($this->links as $link) {
            $result .= '<link rel="' . $link['rel'] . '" href="' . $link['href'] . '"'
                . (empty($link['type']) ? '' : ' type="' . $link['type'] . '"')
                . (empty($link['title']) ? '' : ' title="' . $link['title'] . '"')
                . ' />' . "\n";
        }
        echo $result;
    }

    /**
     * Adds a Javascript file to the setHeader.
     *
     * @param string $file        The path of the javascript file.
     * @param string $condition   The condition of the loading.
     *
     * @return void
     */
    public function addHeaderJavaScript($file, $condition = '')
    {
        $this->headerJavaScripts[$condition][] = $file;
    }

    /**
     * Displays the javascripts in the setHeader (<var>script</var> tags).
     *
     * @return void
     */
    protected function renderHeaderJavaScripts()
    {
        echo $this->getRenderedJavascripts($this->headerJavaScripts);
    }

    /**
     * Adds a Javascript file to the footer.
     *
     * @param string $file        The path of the javascript file.
     * @param string $condition   The condition of the loading.
     *
     * @return void
     */
    public function addFooterJavaScript($file, $condition = '')
    {
        $this->footerJavaScripts[$condition][] = $file;
    }

    /**
     * Displays the javascripts in the footer (<var>script</var> tags).
     *
     * @return void
     */
    protected function renderFooterJavaScripts()
    {
        echo $this->getRenderedJavascripts($this->footerJavaScripts);
    }

    /**
     * Renders the script tags and returns them.
     *
     * @param array $javaScripts   Array contains the javascript files by conditions.
     *
     * @return string
     */
    protected function getRenderedJavascripts(array $javaScripts)
    {
        $result = '';

        foreach ($javaScripts as $condition => $files) {
            array_unique($files);

            $result .= !empty($condition) ? '<!--[if ' . $condition . ']>' . "\n" : '';
            foreach ($files as $file) {
                $result .= '<script type="text/javascript" src="' . $file . '"></script>' . "\n";
            }
            $result .= !empty($condition) ? '<![endif]-->' . "\n" : '';
        }

        return $result;
    }

    /**
     * Adds a javascript variable declaration to the footer.
     *
     * Neither the name nor the value is escaped, they should be passed as already escaped variables. If using the
     * treat as string option, the value should be escaped for single quotes.
     *
     * @param string $name            The name of the variable.
     * @param string $value           The value of the variable. Must be a string that is the valid javascript
     *                                representation of the value.
     * @param bool   $treatAsString   If TRUE, the value will be treated as a string and placed within single quotes.
     *
     * @return void
     */
    public function addJavaScriptVariable($name, $value, $treatAsString)
    {
        if ($treatAsString) {
            $value = '\'' . $value . '\'';
        }
        $this->footerJavaScriptvariables[$name] = $value;
    }

    /**
     * Displays the javascript variables in the footer.
     *
     * @return void
     */
    protected function renderFooterJavaScriptVariables()
    {
        if (empty($this->footerJavaScriptvariables)) {
            return;
        }
        $result = '<script type="text/javascript">';
        foreach ($this->footerJavaScriptvariables as $variableName => $value) {
            $result .= 'var ' . $variableName . ' = ' . $value . ";\n";
        }
        echo $result . '</script>';
    }

    /**
     * Adds a CSS file to the page.
     *
     * @param string $file        The path of the CSS file.
     * @param string $condition   The condition of the loading.
     * @param string $media       The name of the media.
     *
     * @return void
     */
    public function addStyleSheet($file, $condition = '', $media = '')
    {
        $this->styleSheets[$condition][] = [
            self::STYLESHEET_INDEX_FILE  => $file,
            self::STYLESHEET_INDEX_MEDIA => $media,
        ];
    }

    /**
     * Displays the CSS files (<var>link</var> tags).
     *
     * @return void
     */
    protected function renderStyleSheets()
    {
        $result = '';

        foreach ($this->styleSheets as $condition => $files) {
            $result .= !empty($condition) ? '<!--[if ' . $condition . ']>' . "\n" : '';
            foreach ($files as $file) {
                $result .= '<link rel="stylesheet" type="text/css" href="'
                    . $file[self::STYLESHEET_INDEX_FILE] . '" '
                    . (!empty($file[self::STYLESHEET_INDEX_FILE])
                        ? 'media="' . $file[self::STYLESHEET_INDEX_MEDIA] . '" '
                        : '')
                    . '/>' . "\n";
            }
            $result .= !empty($condition) ? '<![endif]-->' . "\n" : '';
        }

        echo $result;
    }

    /**
     * Opens a slot with the given name.
     *
     * After the call, all of the generated content will be stored in the opened slot until it closed.
     *
     * @param string $name   The name od the slot.
     *
     * @return void
     */
    public function beginSlot($name)
    {
        array_push($this->activeSlotNames, $name);
        ob_start();
    }

    /**
     * Closes the slot, and stores the content.
     *
     * @param bool $overwrite   If TRUE and a slot already exists with the given name, it will overwrite the content,
     *                          if FALSE it will append to the existent content.
     *
     * @return void
     */
    public function endSlot($overwrite = true)
    {
        $activeSlotName = array_pop($this->activeSlotNames);
        if ($activeSlotName === null) {
            return;
        }

        if (!isset($this->slots[$activeSlotName]) || $overwrite) {
            $this->slots[$activeSlotName] = ob_get_clean();
        } else {
            $this->slots[$activeSlotName] .= ob_get_clean();
        }
    }

    /**
     * Checks if the given slot exists.
     *
     * @param string $name   The name of the slot.
     *
     * @return bool
     */
    public function hasSlot($name)
    {
        return isset($this->slots[$name]);
    }

    /**
     * Displays the slot.
     *
     * @param string $name   The name of the slot.
     *
     * @return void
     */
    public function renderSlot($name)
    {
        echo isset($this->slots[$name]) ? $this->slots[$name] : '';
    }
}
