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
 * Block class
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class Block extends ViewAbstract {

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
     * Constructor.
     *
     * Final, so the signature stays the same for all blocks. Any initialization code should be placed in the
     * init() method.
     */
    final public function __construct() {
        $this->init();
        parent::__construct();
    }

    /**
     * Called from the constructor.
     */
    protected function init() {
        // Implemented in descendant classes if needed.
    }

    /**
     * Sets a parameter for the block
     *
     * @param string $name       The name of the parameter.
     * @param mixed  $value      The value. It will be escaped according to the view used.
     * @param mixed  $rawValue   If set, the value is considered to be already escaped.
     */
    public function set($name, $value, $rawValue) {
        $this->params[$name] = $value;
        $this->rawParams[$name] = $rawValue;
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

}