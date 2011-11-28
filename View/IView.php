<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\View;

/**
 * View interface
 *
 * @package    YapepBase
 * @subpackage View
 */
interface IView {

    /**
     * Renders the view and returns it.
     *
     * @param string $contentType   The content type of the response.
     *
     * @return string   The rendered view
     */
    public function render($contentType);
}