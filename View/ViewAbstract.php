<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\View;

use YapepBase\Application;
use YapepBase\Exception\ViewException;
use YapepBase\Mime\MimeType;
use YapepBase\Config;

/**
 * ViewAbstract class what should be extended by every View class.
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class ViewAbstract implements IView {
    /**
     * Stores the content type
     *
     * @var string
     */
    protected $contentType;

    /**
     * Does the actual rendering.
     */
    abstract protected function renderContent();

    /**
     * Renders the view and prints it.
     *
     * @return void
     */
    public function render() {
        $this->renderContent();
    }

    /**
     * Returns the rendered content.
     *
     * It returns the same as the {@link render()} prints.
     *
     * @return string
     */
    public function __toString() {
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    /**
     * Sets the contentType of the View.
     *
     * @param string $contentType   {@uses \YapepBase\Mime\MimeType::*}
     *
     * @return void
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    /**
     * Displays th given block
     *
     * @param \YapepBase\View\BlockAbstract $block   The block.
     *
     * @return void
     */
    protected function renderBlock(BlockAbstract $block) {
        $block->render();
    }

    /**
     * Returns the the value registered to the given key.
     *
     * @param string $key   The name of the key.
     * @param bool   $raw   if TRUE it will return the raw (unescaped) data.
     *
     * @return mixed   The data stored with the given key.
     */
    public function get($key, $raw = false) {
        return Application::getInstance()->getDiContainer()->getViewDo()->get($key, $raw);
    }

    /**
     * Checks the given key if it has a value.
     *
     * @param string $key          The name of the key.
     * @param bool   $checkIsSet   If TRUE it checks the existense of the key.
     *
     * @return bool   FALSE if it has a value/exist, TRUE if not.
     */
    public function checkIsEmpty($key, $checkIsSet = false) {
        return Application::getInstance()->getDiContainer()->getViewDo()->checkIsEmpty($key, $checkIsSet);
    }

    /**
     * Checks if the value is an array.
     *
     * @param string $key   The name of the key.
     *
     * @return bool   TRUE if its an array, FALSE if not.
     */
    public function checkIsArray($key) {
        return Application::getInstance()->getDiContainer()->getViewDo()->checkIsArray($key);
    }
}