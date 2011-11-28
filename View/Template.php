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
 * Template class
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class Template extends ViewAbstract {

    /**
     * Stores the raw (non-escaped) version of the params.
     *
     * @var array
     */
    private $rawParams = array();

    /**
     * Stores the escaped params.
     *
     * @var array
     */
    private $params = array();

    /**
     * Stores the layout object for the view
     *
     * @var \YapepBase\View\Layout
     */
    private $layout;

    /**
     * Renders the view and returns it.
     *
     * @param string $contentType   The content type of the response.
     *                              {@uses \YapepBase\Response\HttpResponse::CONTENT_TYPE_*}
     * @param bool   $return        If TRUE, the method will return the output, otherwise it will print it.
     *
     * @return string   The rendered view or NULL if not returned
     */
    public function render($contentType, $return = true) {
        $this->contentType;
        if (empty($this->params) && !empty($this->rawParams)) {
            $this->params = $this->escape($this->rawParams);
        }

        if (empty($this->layout)) {
            return parent::render($contentType, $return);
        } else {
            $output = parent::render($contentType, true);
            $this->layout->setTemplateOutput($output);
            return $this->layout->render($contentType, $return);
        }
    }

    /**
     * Sets a parameter for the view
     *
     * @param string $name       The name of the parameter.
     * @param mixed  $value      The value. It will be escaped according to the view used.
     * @param mixed  $rawValue   If set, the value is considered to be already escaped.
     */
    public function set($name, $value) {
        $this->rawParams[$name] = $value;
    }

    /**
     * Returns the escaped version of a param.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function get($name) {
        return $this->params[$name];
    }

    /**
     * Returns the raw (non-escaped) version of a param.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getRaw($name) {
        return $this->rawParams[$name];
    }

    /**
     * Sets the layout for the view.
     *
     * @param \YapepBase\View\Layout $layout
     */
    public function setLayout(Layout $layout) {
        $this->layout = $layout;
    }
}