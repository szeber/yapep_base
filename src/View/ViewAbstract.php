<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage View
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\View;


use YapepBase\Application;
use YapepBase\Exception\Exception;
use YapepBase\Storage\IStorage;

/**
 * ViewAbstract class what should be extended by every View class.
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class ViewAbstract
{

    /**
     * The ViewDo instance used by the view
     *
     * @var \YapepBase\View\ViewDo
     */
    protected $viewDo;

    /**
     * Stores the content type
     *
     * @var string
     */
    protected $contentType;

    /**
     * Storage object which will be used for caching the rendered view object.
     *
     * @var \YapepBase\Storage\IStorage
     */
    private $storage;

    /**
     * The key which will be used for storing the rendered view object.
     *
     * @var string
     */
    private $storageKey;

    /**
     * Time to leave in seconds for storing the view object.
     *
     * @var int
     */
    private $storageTtl;

    /**
     * Does the actual rendering.
     *
     * @return void
     */
    abstract protected function renderContent();

    /**
     * Renders the view and prints it.
     *
     * @return void
     */
    protected function render()
    {
        $this->renderContent();
    }

    /**
     * Returns the rendered content.
     *
     * It returns the same as the {@link render()} prints.
     *
     * @return string
     */
    public function toString()
    {
        $result = $this->getFromStorage();

        if ($result === false) {
            ob_start();
            $this->render();
            $result = ob_get_clean();

            // If an exception occurs, we don't want to cache the output.
            $this->setToStorage($result);
        }
        return $result;
    }

    /**
     * Sets the contentType of the View.
     *
     * @param string $contentType {@uses \YapepBase\Mime\MimeType::*}
     *
     * @return void
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Displays the given block
     *
     * @param \YapepBase\View\BlockAbstract $block The block.
     *
     * @return void
     */
    protected function renderBlock(BlockAbstract $block)
    {
        // The View Object can have a layout, so we give it to the block as well to provide access
        if ($this instanceof IHasLayout && $this->checkHasLayout()) {
            $block->setLayout($this->getLayout());
        } // The current View Object is a Layout, so we pass it to the block as well
        elseif ($this instanceof LayoutAbstract) {
            $block->setLayout($this);
        }

        echo $block->toString();
    }

    /**
     * Returns the the value registered to the given key.
     *
     * @param string $key The name of the key.
     * @param bool   $raw if TRUE it will return the raw (unescaped) data.
     *
     * @return mixed   The data stored with the given key.
     */
    public function get($key, $raw = false)
    {
        return $this->getViewDo()->get($key, $raw);
    }

    /**
     * Checks the given key if it has a value.
     *
     * @param string $key        The name of the key.
     * @param bool   $checkIsSet If TRUE it checks the existense of the key.
     *
     * @return bool   FALSE if it has a value/exist, TRUE if not.
     */
    public function checkIsEmpty($key, $checkIsSet = false)
    {
        return $this->getViewDo()->checkIsEmpty($key, $checkIsSet);
    }

    /**
     * Checks if the value is an array.
     *
     * @param string $key The name of the key.
     *
     * @return bool   TRUE if its an array, FALSE if not.
     */
    public function checkIsArray($key)
    {
        return $this->getViewDo()->checkIsArray($key);
    }

    /**
     * Sets the view DO instance used by the view.
     *
     * @param \YapepBase\View\ViewDo $viewDo The ViewDo instance to use.
     *
     * @return void
     */
    protected function setViewDo(ViewDo $viewDo)
    {
        $this->viewDo = $viewDo;
    }

    /**
     * Returns the currently used view DO instance.
     *
     * @return \YapepBase\View\ViewDo
     */
    protected function getViewDo()
    {
        if (empty($this->viewDo)) {
            $this->viewDo = Application::getInstance()->getDiContainer()->getViewDo();
        }
        return $this->viewDo;
    }

    /**
     * Sets the storage object which will be used for cacheing the rendered view.
     *
     * @param \YapepBase\Storage\IStorage $storage        The object for caching.
     * @param array                       $keyModifiers   Associative array which holds the keys and values,
     *                                                    what will take into consideration in the caching process.
     * @param int                         $ttl            Time to leave in seconds.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\Exception   If the storage has benn already set.
     */
    protected function setStorage(IStorage $storage, array $keyModifiers, $ttl)
    {
        if ($this->storage !== null) {
            throw new Exception('Storage already set');
        }

        $this->storage    = $storage;
        $this->storageKey = $this->generateKeyForStorage($keyModifiers);
        $this->storageTtl = $ttl;
    }

    /**
     * Generates the key for storing the rendered view.
     *
     * @param array $keyModifiers   Associative array which holds the keys and values,
     *                              what will take into consideration in the caching process.
     *
     * @return string   The generated key.
     */
    private function generateKeyForStorage(array $keyModifiers)
    {
        $modifiers = [];
        foreach ($keyModifiers as $fieldName => $value) {
            $modifiers[] = $fieldName . '=' . $value;
        }
        return get_called_class() . '.' . implode('.', $modifiers);
    }

    /**
     * Stores the given data
     *
     * @param string $data The data should be stored.
     *
     * @return void
     */
    private function setToStorage($data)
    {
        if ($this->storage === null) {
            return;
        }

        $this->storage->set($this->storageKey, (string)$data, $this->storageTtl);
    }

    /**
     * Returns the stored data.
     *
     * @return string|bool   The stored data or FALSE if its not stored.
     */
    private function getFromStorage()
    {
        if ($this->storage === null) {
            return false;
        }

        return $this->storage->get($this->storageKey);
    }

    /**
     * Translates the specified string.
     *
     * @param string $string     The string.
     * @param array  $parameters The parameters for the translation.
     * @param string $language   The language.
     *
     * @return string
     */
    protected function _($string, $parameters = [], $language = null)
    {
        return Application::getInstance()->getI18nTranslator()->translate(get_class($this), $string, $parameters,
            $language);
    }
}
