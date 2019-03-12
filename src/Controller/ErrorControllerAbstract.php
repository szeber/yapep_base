<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Controller;

use YapepBase\Exception\ControllerException;

/**
 * Base class for error controllers.
 */
abstract class ErrorControllerAbstract extends HttpControllerAbstract
{
    /**
     * Runs before the action.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ControllerException   On error.
     */
    protected function before()
    {
        // Clear all previous output before rendering
        $this->response->clearAllOutput();
        parent::before();
    }

    /**
     * Runs on page not found (404) errors
     *
     * @return \YapepBase\View\TemplateAbstract|string
     */
    abstract protected function do404();

    /**
     * Runs on internal server error (500) erorrs
     *
     * @return \YapepBase\View\TemplateAbstract|string
     */
    abstract protected function do500();

    /**
     * Runs the specified action
     *
     * @param int $errorCode The name of the action (without the controller specific prefix)
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error.
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \YapepBase\Exception\RedirectException     On redirections.
     * @throws \Exception                                 On non-framework related errors.
     */
    public function run($errorCode)
    {
        try {
            $this->response->setStatusCode($errorCode);
            parent::run($errorCode);
        } catch (ControllerException $exception) {
            if ($exception->getCode() != ControllerException::ERR_ACTION_NOT_FOUND) {
                // We only handle the exception if it's because of a missing action
                throw $exception;
            }
            // Action not found for the specified error code, log the error and run with 500 instead.
            trigger_error('Action not found for error code: ' . $errorCode, E_USER_WARNING);
            $this->run(500);
        }
    }
}
