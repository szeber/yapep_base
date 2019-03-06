<?php
declare(strict_types=1);

namespace YapepBase\Request;

use YapepBase\Request\Entity\CustomParams;

interface IRequest
{
    /**
     * Returns the target of the request. (eg the URI for HTTP requests)
     */
    public function getTarget(): string;

    /**
     * Returns the method of the request
     */
    public function getMethod(): string;

    /**
     * Returns the protocol used in the request.
     */
    public function getProtocol(): string;

    /**
     * Returns the Custom Param object
     */
    public function getCustomParam(): CustomParams;
}
