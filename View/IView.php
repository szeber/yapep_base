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

/**
 * Interface what should be implemented by every View
 *
 * @package    YapepBase
 * @subpackage View
 */
interface IView {

    /**
     * Renders the view and prints it.
     *
     * @return void
     */
    public function render();
}