<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\Controller\Exception\ActionNotFoundException;
use YapepBase\View\IRenderable;

/**
 * Base class for error controllers.
 */
abstract class ErrorControllerAbstract extends ControllerAbstract
{
    protected function runBeforeAction(): void
    {
        $this->response->getOutputHandler()->clear();
        parent::runBeforeAction();
    }

    /**
     * Runs on page not found (404) errors
     */
    abstract protected function do404(): IRenderable;

    /**
     * Runs on internal server error (500) errors
     */
    abstract protected function do500(): IRenderable;

    public function run(string $errorCode): void
    {
        try {
            $this->response->setStatusCode((int)$errorCode);
            parent::run($errorCode);
        } catch (ActionNotFoundException $exception) {
            // Action not found for the specified error code, log the error and run with 500 instead.
            trigger_error($exception->getMessage(), E_USER_WARNING);
            $this->run('500');
        }
    }
}
