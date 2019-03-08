<?php
declare(strict_types=1);

namespace YapepBase\Controller;


use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * Controller interface
 *
 * Configuration options:
 * <ul>
 *   <li>system.performStrictControllerActionNameValidation: If this option is TRUE, the action's name will be
 *                                                           validated in a case sensitive manner. This is recommended
 *                                                           for development, but not recommended for production as it
 *                                                           can cause errors, and will somewhat impact the performance.
 *                                                           Optional, defaults to FALSE.</li>
 * </ul>
 */
interface IController
{
    public function setRequest(IRequest $request): void;

    public function setResponse(IResponse $response): void;

    /**
     * Runs the specified action
     *
     * @param string $action The name of the action (without the controller specific prefix)
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\RedirectException     On redirects. These should not be treated as errors!
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    public function run(string $action): void;
}
