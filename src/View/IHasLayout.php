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
 * Interface which tells you that the implementing class can have a layout.
 */
interface IHasLayout
{
    /**
     * Sets the layout.
     *
     * @param \YapepBase\View\LayoutAbstract $layout   The layout object.
     *
     * @return void
     */
    public function setLayout(LayoutAbstract $layout);

    /**
     * Returns the current layout
     *
     * @return \YapepBase\View\LayoutAbstract
     */
    public function getLayout();

    /**
     * Checks if the object has a layout set.
     *
     * @return bool   TRUE if it has, FALSE otherwise.
     */
    public function checkHasLayout();
}
