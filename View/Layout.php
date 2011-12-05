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
 * Layout class
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class Layout extends ViewAbstract {

    /**
     * Stores the template output
     *
     * @var string
     */
    private $templateOutput;

    /**
     * Sets the template output
     *
     * @param string $output
     */
    public function setTemplateOutput($output) {
        $this->templateOutput = $output;
    }

    /**
     * Returns the template output
     *
     * @return string
     */
    protected function getTemplateOutput() {
        return $this->templateOutput;
    }
}