<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\View;

/**
 * BlockAbstract class, should be extended by every Block.
 */
abstract class BlockAbstract extends ViewAbstract implements IHasLayout
{
    /**
     * Holds the layout.
     *
     * @var \YapepBase\View\LayoutAbstract
     */
    protected $layout = null;

    /**
     * Sets the layout.
     *
     * @param \YapepBase\View\LayoutAbstract $layout   The layout object.
     *
     * @return void
     */
    public function setLayout(LayoutAbstract $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Returns the current layout
     *
     * @return \YapepBase\View\LayoutAbstract
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Checks if the object has a layout set.
     *
     * @return bool   TRUE if it has, FALSE otherwise.
     */
    public function checkHasLayout()
    {
        return !is_null($this->layout);
    }
}
