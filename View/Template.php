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
 * Template class. Any subclasses are direct templates with HTML code in them. Variables to the template must be
 * defined as local variables. Reserved variables are:
 * <ul>
 *  <li>layout</li>
 *  <li>variables</li>
 *  <li>required</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class Template extends ViewAbstract {

    /**
     * Stores the layout object for the view.
     *
     * @var \YapepBase\View\Layout
     */
    protected $layout;
    
    /**
     * All template variables as keys (cached), set marks as values. If a variable has been set, it is set to true.
     * @var array
     */
    protected $variables;
    
    /**
     * Required local variables
     * @var array
     */
    protected $required = array();
    
    protected $raw;
    
    /**
     * Caches local variables.
     */
    private function cacheVariables() {
        if (!is_array($this->variables)) {
            $vars = get_object_vars($this);
            unset($vars['layout']);
            unset($vars['variables']);
            unset($vars['required']);
            foreach ($vars as &$var) {
                $var = false;
            }
            $this->variables = $vars;
        }
    }
    
    public function hasVariable($var) {
        $this->cacheVariables();
        if (array_key_exists($var, $this->variables)) {
            return true;
        } else {
            return false;
        }
    }
    
    protected function markSet($var) {
        $this->cacheVariables();
        if ($this->hasVariable($var)) {
            $this->variables[$var] = true;
        } else {
            throw new \YapepBase\Exception\ParameterException("Template " . get_class($this) . " has no parameter " . $var);
        }
    }

    public function isRequiredVariable($var) {
        $this->cacheVariables();
        if ($this->hasVariable($var)) {
            if (array_search($var, $this->required)) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \YapepBase\Exception\ParameterException("Template " . get_class($this) . " has no parameter " . $var);
        }
    }

    public function set($var, $value) {
        $this->cacheVariables();
        if ($this->hasVariable($var)) {
            $this->$var = $value;
            $this->raw[$var] = $value;
        } else {
            throw new \YapepBase\Exception\ParameterException("Template " . get_class($this) . " has no parameter " . $var);
        }
    }
    
    public function get($var, $raw = false) {
        $this->cacheVariables();
        if ($this->hasVariable($var)) {
            if ($raw) {
                return $this->raw[$var];
            } else {
                return $this->$var;
            }
        } else {
            throw new \YapepBase\Exception\ParameterException("Template " . get_class($this) . " has no parameter " . $var);
        }
    }
    
    /**
     * Renders the view and returns it.
     *
     * @param string $contentType   The content type of the response.
     *                              {@uses \YapepBase\Mime\MimeType::*}
     * @param bool   $return        If TRUE, the method will return the output, otherwise it will print it.
     *
     * @return string   The rendered view or NULL if not returned
     */
    public function render($contentType, $return = true) {
        $this->contentType = $contentType;
        foreach ($this->required as $param) {
            if ($this->variables[$param] == false) {
                throw new \YapepBase\Exception\ParameterException($param . " is required in template " . get_class($this));
            }
        }
        
        foreach ($this->variables as $variable => $set) {
            if ($set) {
                $this->$variable = $this->escape($this->$variable);
            }
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
     * Sets the layout for the view.
     *
     * @param \YapepBase\View\Layout $layout
     */
    public function setLayout(Layout $layout) {
        $this->layout = $layout;
    }
}