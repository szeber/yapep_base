<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\Controller\Exception\Exception;
use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Exception\RedirectException;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;

/**
 * Controller interface
 */
interface IController
{
    /**
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setRequest(IRequest $request);

    /**
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setResponse(IResponse $response);

    /**
     * Runs the specified action
     *
     * @throws Exception
     * @throws RedirectException
     * @throws Exception
     */
    public function run(string $action): void;
}
