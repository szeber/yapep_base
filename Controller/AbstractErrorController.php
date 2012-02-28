<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;
use YapepBase\Exception\ControllerException;

/**
 * AbstractErrorController class
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class AbstractErrorController extends HttpController {

    /**
     * Runs on page not found (404) errors
     *
     * @return \YapepBase\View\Template|string
     */
    abstract protected function do404();

    /**
     * Runs on internal server error (500) erorrs
     *
     * @return \YapepBase\View\Template|string
     */
    abstract protected function do500();

    /**
     * Runs the specified action
     *
     * @param int $errorCode   The name of the action (without the controller specific prefix)
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error.
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \YapepBase\Exception\RedirectException     On redirections.
     * @throws \Exception                                 On non-framework related errors.
     */
    public function run($errorCode) {
        try {
            $this->response->setStatusCode($errorCode);
            parent::run($errorCode);
        } catch (ControllerException $exception) {
            if ($exception->getCode() != ControllerException::ERR_ACTION_NOT_FOUND) {
                // We only handle the exception if it's because of a missing action
                throw $exception;
            }
            // Action not found for the specified error code, log the error and run with 500 instead.
            trigger_error('Action not found for error code: ' . $errorCode);
            $this->run(500);
        }
    }

}