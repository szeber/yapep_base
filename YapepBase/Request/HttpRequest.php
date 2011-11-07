<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Request
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;

use YapepBase\UtilityFunctions;

/**
 * HttpRequest class
 *
 * @package    YapepBase
 * @subpackage Request
 */
class HttpRequest implements IRequest {

    protected $getParams;
    protected $postParams;
    protected $cookies;

    public function __construct() {
        if (get_magic_quotes_gpc()) {
            $this->getParams = UtilityFunctions::recursiveStripSlashes($_GET);
            $this->postParams = UtilityFunctions::recursiveStripSlashes($_POST);
            $this->cookies = UtilityFunctions::recursiveStripSlashes($_COOKIE);
        } else {
            $this->getParams = $_GET;
            $this->postParams = $_POST;
            $this->cookies = $_COOKIE;
        }
    }

    public function getGet($name, $default = null) {
        if (isset($this->getParams[$name])) {
            return $this->getParams[$name];
        }
        return $default;
    }

    public function getPost($name, $default = null) {
        if (isset($this->postParams[$name])) {
            return $this->postParams[$name];
        }
        return $default;
    }

    public function getCookie($name, $default = null) {
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }
        return $default;
    }

    public function getHeader() {

    }

    public function getEnv() {

    }

    public function get($name, $default = null, $source = 'GP') {
        $source = strtoupper($source);
        $result = $default;

        if (strstr($source, 'G') && isset($this->getParams[$name])) {
            $result = $this->getParams[$name];
        }

        if (strstr($source, 'P') && isset($this->postParams[$name])) {
            $result = $this->getParams[$name];
        }

        if (strstr($source, 'C') && isset($this->cookies[$name])) {
            $result = $this->cookies[$name];
        }

        return $result;
    }

    public function getTarget() {

    }

    public function isAjaxRequest() {

    }

    public function isIe() {

    }

    public function isGecko() {

    }

    public function isWebkit() {

    }
}