<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Exception;

/**
 * RedirectException class.
 *
 * Not descendant of Yapep\Exception\Exception, and it should only be catched by the Application
 * or a controller if neccessary.
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class RedirectException extends \Exception {

    protected $controller;

    protected $action;

    public function __construct($controller, $action, $previous) {
        $message = 'Redirecting to ' . $controller . '/' . $action;
        $this->controller = $controller;
        $this->action = $action;

        parent::__construct($message, 0, $previous);
    }
}